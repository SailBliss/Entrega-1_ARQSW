<?php

namespace App\Console\Commands;

use App\Models\WatchCertificate;
use App\Services\BlockchainService;
use Illuminate\Console\Command;

class VerifyCertificateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain:verify 
                            {hash : The SHA-256 certificate hash to verify}
                            {--json : Output result in raw JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query all Docker blockchain nodes to verify certificate authenticity and network consensus';

    /**
     * Execute the console command.
     */
    public function handle(BlockchainService $blockchainService): int
    {
        $hash = $this->argument('hash');
        $jsonOnly = $this->option('json');

        if (! $jsonOnly) {
            $this->info('====================================================');
            $this->info('    BLOCKCHAIN P2P NETWORK CERTIFICATE VERIFIER     ');
            $this->info('====================================================');
            $this->line("Target Certificate Hash: <comment>{$hash}</comment>");
            $this->line('Querying P2P nodes (Store, Auditor, Notary) in Docker...');
            $this->newLine();
        }

        // Query all nodes
        $consensusResult = $blockchainService->verifyConsensusAcrossNodes($hash);

        // Also check local database record if present
        $localCertificate = WatchCertificate::where('certificate_hash', $hash)->first();

        if ($jsonOnly) {
            $output = array_merge($consensusResult, [
                'local_database_record' => $localCertificate ? [
                    'serial_number' => $localCertificate->serial_number,
                    'status' => $localCertificate->status,
                    'created_at' => $localCertificate->created_at?->toIso8601String(),
                ] : null,
            ]);
            $this->line(json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return Command::SUCCESS;
        }

        $tableRows = [];
        $payloadData = null;

        foreach ($consensusResult['nodes'] as $nodeName => $nodeInfo) {
            $online = $nodeInfo['online'] ? 'ONLINE' : 'OFFLINE';
            $verified = ! empty($nodeInfo['data']['verified']) ? 'VALID' : 'INVALID';
            $blockIndex = $nodeInfo['data']['block_index'] ?? 'N/A';
            $blockHash = isset($nodeInfo['data']['block_hash']) ? substr($nodeInfo['data']['block_hash'], 0, 16).'...' : 'N/A';

            if (! empty($nodeInfo['data']['serial_number'])) {
                $payloadData = ! empty($nodeInfo['data']['payload']) ? $nodeInfo['data']['payload'] : $nodeInfo['data'];
            }

            $tableRows[] = [
                ucfirst($nodeName),
                $nodeInfo['node_url'],
                $online,
                $verified,
                $blockIndex,
                $blockHash,
            ];
        }

        $this->table(['Node', 'URL', 'Status', 'Verdict', 'Block #', 'Block Hash (prefix)'], $tableRows);
        $this->newLine();

        if ($consensusResult['consensus']) {
            $this->info(">>> CONSENSUS CONFIRMED: {$consensusResult['verified_nodes_count']}/{$consensusResult['total_nodes']} NODES IN AGREEMENT <<<");
            $this->line('<info>The certificate is authentic, immutable, and synchronized across the P2P network.</info>');
            $this->line("Block Hash: <comment>{$consensusResult['consistent_block_hash']}</comment>");
        } else {
            $this->error(">>> WARNING: CONSENSUS NOT REACHED ({$consensusResult['verified_nodes_count']}/{$consensusResult['total_nodes']} nodes verified) <<<");
            $this->line('The transaction might still be unmined, or the hash was not found on some nodes.');
        }

        if ($payloadData) {
            $this->newLine();
            $this->info('--- CERTIFICATE METADATA EXTRACTED FROM BLOCKCHAIN ---');
            $this->line('Serial Number: '.($payloadData['serial_number'] ?? 'N/A'));
            $this->line('Watch:         '.($payloadData['watch_brand'] ?? '').' '.($payloadData['watch_name'] ?? 'N/A'));
            $this->line('Price:         $'.number_format((float) ($payloadData['watch_price'] ?? 0), 2));
            $this->line('Buyer:         '.($payloadData['buyer_name'] ?? 'N/A').' ('.($payloadData['buyer_email'] ?? 'N/A').')');
            $this->line('Order ID:      #'.($payloadData['order_id'] ?? 'N/A'));
            $this->line('Issued At:     '.($payloadData['issued_at'] ?? 'N/A'));
        }

        $this->info('====================================================');

        return $consensusResult['consensus'] ? Command::SUCCESS : Command::FAILURE;
    }
}
