<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Watch;
use App\Models\WatchCertificate;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlockchainService
{
    protected string $primaryNode;
    protected array $nodes;
    protected int $timeout;

    public function __construct()
    {
        $this->primaryNode = config('blockchain.primary_node', 'http://127.0.0.1:5001');
        $this->nodes = config('blockchain.nodes', [
            'store' => 'http://127.0.0.1:5001',
            'auditor' => 'http://127.0.0.1:5002',
            'notary' => 'http://127.0.0.1:5003',
        ]);
        $this->timeout = (int) config('blockchain.timeout', 15);
    }

    /**
     * Generate a unique serial number for the physical watch certificate.
     */
    public function generateSerialNumber(Watch $watch, ?int $orderId = null, ?int $itemId = null): string
    {
        $rand = strtoupper(Str::random(6));
        $watchId = $watch->id ?? 0;
        $orderId = $orderId ?? 0;
        $itemId = $itemId ?? 0;

        return sprintf('SN-W%d-O%d-I%d-%s', $watchId, $orderId, $itemId, $rand);
    }

    /**
     * Compute SHA-256 hash of the canonical certificate payload.
     */
    public function generateCertificateHash(array $payload): string
    {
        ksort($payload);
        $canonicalJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', $canonicalJson);
    }

    /**
     * Issue a certificate, save to database, and mine into the blockchain network.
     */
    public function issueAndMine(Order $order, OrderItem $item): WatchCertificate
    {
        $item->loadMissing('watch');
        $order->loadMissing('user');

        $watch = $item->watch;
        $serialNumber = $this->generateSerialNumber($watch, $order->id, $item->id);

        $payload = [
            'serial_number' => $serialNumber,
            'watch_id' => $watch->id,
            'watch_name' => $watch->name,
            'watch_brand' => $watch->brand,
            'watch_price' => (float) $item->price,
            'order_id' => $order->id,
            'buyer_id' => $order->user_id,
            'buyer_name' => $order->user->name ?? 'Unknown Buyer',
            'buyer_email' => $order->user->email ?? 'unknown@example.com',
            'issued_at' => now()->toIso8601String(),
        ];

        $certificateHash = $this->generateCertificateHash($payload);

        $certificate = WatchCertificate::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'watch_id' => $watch->id,
            'user_id' => $order->user_id,
            'serial_number' => $serialNumber,
            'certificate_hash' => $certificateHash,
            'node_url' => $this->primaryNode,
            'status' => 'pending',
            'payload' => $payload,
        ]);

        try {
            // 1. Submit transaction to primary node
            $txResponse = Http::timeout($this->timeout)->post("{$this->primaryNode}/transactions/new", array_merge($payload, [
                'order_id' => $order->id,
                'watch_id' => $watch->id,
                'serial_number' => $serialNumber,
                'certificate_hash' => $certificateHash,
                'payload' => $payload,
            ]));

            if (! $txResponse->successful()) {
                Log::warning("Failed to submit blockchain transaction: {$txResponse->body()}");
                return $certificate;
            }

            // 2. Trigger mining on primary node (which will broadcast to peers)
            $mineResponse = Http::timeout($this->timeout)->post("{$this->primaryNode}/mine");

            if ($mineResponse->successful()) {
                $mineData = $mineResponse->json();
                $block = $mineData['block'] ?? null;

                if ($block) {
                    $certificate->update([
                        'block_index' => $block['index'] ?? null,
                        'block_hash' => $block['hash'] ?? null,
                        'status' => 'mined',
                    ]);
                }
            } else {
                Log::warning("Mining failed on primary node: {$mineResponse->body()}");
            }
        } catch (Exception $e) {
            Log::error("Blockchain network communication error: {$e->getMessage()}");
            // Graceful degradation: purchase succeeded, certificate marked pending
        }

        return $certificate->fresh();
    }

    /**
     * Verify certificate on a single node.
     */
    public function verifyOnNode(string $certificateHash, ?string $nodeUrl = null): array
    {
        $nodeUrl = $nodeUrl ?: $this->primaryNode;

        try {
            $response = Http::timeout($this->timeout)->get("{$nodeUrl}/verify/{$certificateHash}");

            if ($response->successful()) {
                $data = $response->json();
                $data['verified'] = !empty($data['valid']) || !empty($data['verified']);

                return [
                    'online' => true,
                    'node_url' => $nodeUrl,
                    'status_code' => $response->status(),
                    'data' => $data,
                ];
            }

            return [
                'online' => true,
                'node_url' => $nodeUrl,
                'status_code' => $response->status(),
                'data' => $response->json() ?? ['verified' => false, 'valid' => false, 'message' => 'Not found'],
            ];
        } catch (Exception $e) {
            return [
                'online' => false,
                'node_url' => $nodeUrl,
                'error' => $e->getMessage(),
                'data' => ['verified' => false, 'valid' => false, 'message' => 'Node unreachable'],
            ];
        }
    }

    /**
     * Query all registered nodes to verify consensus and certificate authenticity across P2P network.
     */
    public function verifyConsensusAcrossNodes(string $certificateHash, ?array $nodes = null): array
    {
        $targetNodes = $nodes ?: $this->nodes;
        $results = [];
        $hashes = [];
        $verifiedCount = 0;
        $totalNodes = count($targetNodes);

        foreach ($targetNodes as $name => $url) {
            $nodeResult = $this->verifyOnNode($certificateHash, $url);
            $results[$name] = $nodeResult;

            $isValid = !empty($nodeResult['data']['verified']) || !empty($nodeResult['data']['valid']);
            if ($nodeResult['online'] && $isValid) {
                $verifiedCount++;
                $blockHash = $nodeResult['data']['block_hash'] ?? null;
                if ($blockHash) {
                    $hashes[] = $blockHash;
                }
            }
        }

        // Consensus is achieved if at least majority verified AND all reporting hashes are identical
        $uniqueHashes = array_unique($hashes);
        $hasConsensus = ($verifiedCount > 0) && (count($uniqueHashes) === 1) && ($verifiedCount === $totalNodes);

        return [
            'certificate_hash' => $certificateHash,
            'consensus' => $hasConsensus,
            'verified_nodes_count' => $verifiedCount,
            'total_nodes' => $totalNodes,
            'consistent_block_hash' => count($uniqueHashes) === 1 ? ($hashes[0] ?? null) : null,
            'nodes' => $results,
        ];
    }
}
