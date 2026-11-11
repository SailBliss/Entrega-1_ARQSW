<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Watch;
use App\Services\BlockchainService;
use Illuminate\Console\Command;

class PurchaseTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blockchain:purchase-test 
                            {--watch-id= : ID of existing watch to purchase}
                            {--user-id= : ID of existing user making the purchase}
                            {--json-only : Only output raw JSON struct}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a watch purchase, issue a blockchain certificate, mine it into the P2P network, and return a verification struct';

    /**
     * Execute the console command.
     */
    public function handle(BlockchainService $blockchainService): int
    {
        $jsonOnly = $this->option('json-only');

        if (! $jsonOnly) {
            $this->info('====================================================');
            $this->info('    RELOJES LUXURY - BLOCKCHAIN PURCHASE SIMULATOR  ');
            $this->info('====================================================');
        }

        // 1. Resolve User
        $user = null;
        if ($userId = $this->option('user-id')) {
            $user = User::find($userId);
        }
        if (! $user) {
            $user = User::firstOrCreate(
                ['email' => 'cli_buyer@example.com'],
                [
                    'name' => 'CLI Test Buyer',
                    'password' => bcrypt('password123'),
                ]
            );
        }

        // 2. Resolve Watch
        $watch = null;
        if ($watchId = $this->option('watch-id')) {
            $watch = Watch::find($watchId);
        }
        if (! $watch) {
            $watch = Watch::where('stock', '>', 0)->first();
        }
        if (! $watch) {
            $watch = Watch::create([
                'name' => 'Rolex Submariner Date',
                'brand' => 'Rolex',
                'description' => 'Oystersteel with black Cerachrom bezel and black dial',
                'price' => 14500,
                'stock' => 10,
                'image' => 'images/submariner.jpg',
            ]);
        }

        if (! $jsonOnly) {
            $this->line("Buyer: <comment>{$user->name}</comment> ({$user->email})");
            $this->line("Watch: <comment>{$watch->name}</comment> | Brand: <comment>{$watch->brand}</comment> | Price: <comment>\${$watch->price}</comment>");
            $this->newLine();
            $this->info('Creating purchase order in database...');
        }

        // 3. Create Order & Item
        $order = Order::create([
            'user_id' => $user->id,
            'total' => $watch->price,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => $watch->price,
        ]);

        if ($watch->stock > 0) {
            $watch->decrement('stock');
        }

        if (! $jsonOnly) {
            $this->info('Order created successfully! (Order ID: #'.$order->id.')');
            $this->info('Generating authenticity certificate & mining block into P2P Docker network...');
        }

        // 4. Issue and mine on blockchain
        $certificate = $blockchainService->issueAndMine($order, $orderItem);

        $nodeStoreUrl = config('blockchain.nodes.store', 'http://127.0.0.1:5001');
        $curlCmd = "curl -s {$nodeStoreUrl}/verify/{$certificate->certificate_hash}";
        $artisanCmd = "php artisan blockchain:verify {$certificate->certificate_hash}";

        // 5. Structure payload
        $resultStruct = [
            'status' => $certificate->status,
            'order_id' => $order->id,
            'buyer' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'watch' => [
                'id' => $watch->id,
                'name' => $watch->name,
                'brand' => $watch->brand,
                'price' => (float) $watch->price,
            ],
            'certificate' => [
                'serial_number' => $certificate->serial_number,
                'certificate_hash' => $certificate->certificate_hash,
                'block_index' => $certificate->block_index,
                'block_hash' => $certificate->block_hash,
                'mined_at_node' => $certificate->node_url,
                'created_at' => $certificate->created_at?->toIso8601String(),
            ],
            'verification_queries' => [
                'curl_api' => $curlCmd,
                'artisan_cli' => $artisanCmd,
                'direct_api_endpoint' => "{$nodeStoreUrl}/verify/{$certificate->certificate_hash}",
            ],
        ];

        if ($jsonOnly) {
            $this->line(json_encode($resultStruct, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('=================== PURCHASE & MINING RESULT ===================');
        $this->table(
            ['Key', 'Value'],
            [
                ['Order ID', '#'.$order->id],
                ['Watch', "{$watch->brand} - {$watch->name}"],
                ['Serial Number', $certificate->serial_number],
                ['Certificate SHA-256', $certificate->certificate_hash],
                ['Mined Block Index', (string) ($certificate->block_index ?? 'N/A')],
                ['Mined Block Hash', $certificate->block_hash ?? 'N/A'],
                ['Certificate Status', $certificate->status],
            ]
        );

        $this->newLine();
        $this->info('--- QUERY STRUCT (Copy-Paste for Blockchain API query) ---');
        $this->line(json_encode($resultStruct, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->info('You can verify this certificate now by running:');
        $this->comment($curlCmd);
        $this->line('or via Artisan:');
        $this->comment($artisanCmd);
        $this->info('================================================================');

        return Command::SUCCESS;
    }
}
