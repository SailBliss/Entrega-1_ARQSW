<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Watch;
use App\Models\WatchCertificate;
use App\Services\BlockchainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlockchainCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_serial_number_and_hash(): void
    {
        $watch = Watch::create([
            'name' => 'Speedmaster',
            'brand' => 'Omega',
            'description' => 'Moonwatch professional',
            'price' => 7000,
            'stock' => 5,
        ]);

        $service = new BlockchainService();
        $serial = $service->generateSerialNumber($watch, 10, 20);

        $this->assertStringStartsWith('SN-W', $serial);
        $this->assertStringContainsString('-O10-I20-', $serial);

        $hash = $service->generateCertificateHash(['test' => 123]);
        $this->assertEquals(64, strlen($hash)); // SHA-256
    }

    public function test_service_issues_and_mines_certificate_successfully(): void
    {
        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'Submariner',
            'brand' => 'Rolex',
            'description' => 'Classic diver',
            'price' => 12000,
            'stock' => 5,
        ]);

        $order = Order::create(['user_id' => $user->id, 'total' => 12000]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => 12000,
        ]);

        Http::fake([
            'http://127.0.0.1:5001/transactions/new' => Http::response(['message' => 'ok'], 201),
            'http://127.0.0.1:5001/mine' => Http::response([
                'message' => 'Mined',
                'block' => [
                    'index' => 3,
                    'hash' => '000abc123456789',
                ],
            ], 200),
        ]);

        $service = new BlockchainService();
        $cert = $service->issueAndMine($order, $orderItem);

        $this->assertInstanceOf(WatchCertificate::class, $cert);
        $this->assertEquals('mined', $cert->status);
        $this->assertEquals(3, $cert->block_index);
        $this->assertEquals('000abc123456789', $cert->block_hash);
        $this->assertDatabaseHas('watch_certificates', [
            'id' => $cert->id,
            'status' => 'mined',
            'block_index' => 3,
        ]);
    }

    public function test_service_gracefully_degrades_when_nodes_are_offline(): void
    {
        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'G-Shock',
            'brand' => 'Casio',
            'description' => 'Tough solar',
            'price' => 150,
            'stock' => 5,
        ]);

        $order = Order::create(['user_id' => $user->id, 'total' => 150]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => 150,
        ]);

        Http::fake([
            'http://127.0.0.1:5001/*' => Http::response(['error' => 'server error'], 500),
        ]);

        $service = new BlockchainService();
        $cert = $service->issueAndMine($order, $orderItem);

        $this->assertEquals('pending', $cert->status);
        $this->assertNull($cert->block_index);
        $this->assertDatabaseHas('watch_certificates', [
            'id' => $cert->id,
            'status' => 'pending',
        ]);
    }

    public function test_verify_consensus_across_nodes(): void
    {
        $hash = 'testhash123456789';

        Http::fake([
            'http://127.0.0.1:5001/verify/*' => Http::response([
                'valid' => true,
                'block_index' => 2,
                'block_hash' => '000consistenthash',
            ], 200),
            'http://127.0.0.1:5002/verify/*' => Http::response([
                'valid' => true,
                'block_index' => 2,
                'block_hash' => '000consistenthash',
            ], 200),
            'http://127.0.0.1:5003/verify/*' => Http::response([
                'valid' => true,
                'block_index' => 2,
                'block_hash' => '000consistenthash',
            ], 200),
        ]);

        $service = new BlockchainService();
        $result = $service->verifyConsensusAcrossNodes($hash);

        $this->assertTrue($result['consensus']);
        $this->assertEquals(3, $result['verified_nodes_count']);
        $this->assertEquals('000consistenthash', $result['consistent_block_hash']);
    }

    public function test_cli_purchase_test_command(): void
    {
        Http::fake([
            'http://127.0.0.1:5001/transactions/new' => Http::response(['message' => 'ok'], 201),
            'http://127.0.0.1:5001/mine' => Http::response([
                'message' => 'Mined',
                'block' => [
                    'index' => 5,
                    'hash' => '000blockhashfake',
                ],
            ], 200),
        ]);

        $this->artisan('blockchain:purchase-test')
            ->expectsOutputToContain('RELOJES LUXURY - BLOCKCHAIN PURCHASE SIMULATOR')
            ->expectsOutputToContain('Order created successfully')
            ->assertSuccessful();

        $this->assertDatabaseCount('watch_certificates', 1);
    }

    public function test_cli_verify_command(): void
    {
        Http::fake([
            'http://127.0.0.1:5001/verify/*' => Http::response(['valid' => true, 'block_hash' => '000hash', 'block_index' => 2], 200),
            'http://127.0.0.1:5002/verify/*' => Http::response(['valid' => true, 'block_hash' => '000hash', 'block_index' => 2], 200),
            'http://127.0.0.1:5003/verify/*' => Http::response(['valid' => true, 'block_hash' => '000hash', 'block_index' => 2], 200),
        ]);

        $this->artisan('blockchain:verify fakehash123')
            ->expectsOutputToContain('CONSENSUS CONFIRMED')
            ->assertSuccessful();
    }

    public function test_public_web_verification_route(): void
    {
        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'Royal Oak',
            'brand' => 'Audemars Piguet',
            'description' => 'Steel',
            'price' => 35000,
            'stock' => 2,
        ]);
        $order = Order::create(['user_id' => $user->id, 'total' => 35000]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
            'price' => 35000,
        ]);

        $cert = WatchCertificate::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'watch_id' => $watch->id,
            'user_id' => $user->id,
            'serial_number' => 'SN-TEST-1234',
            'certificate_hash' => 'abcdef0123456789abcdef0123456789',
            'block_index' => 4,
            'block_hash' => '000fakeblockhash',
            'node_url' => 'http://127.0.0.1:5001',
            'status' => 'mined',
            'payload' => ['serial_number' => 'SN-TEST-1234'],
        ]);

        Http::fake([
            'http://127.0.0.1:5001/verify/*' => Http::response(['valid' => true, 'block_hash' => '000fakeblockhash', 'block_index' => 4], 200),
            'http://127.0.0.1:5002/verify/*' => Http::response(['valid' => true, 'block_hash' => '000fakeblockhash', 'block_index' => 4], 200),
            'http://127.0.0.1:5003/verify/*' => Http::response(['valid' => true, 'block_hash' => '000fakeblockhash', 'block_index' => 4], 200),
        ]);

        $response = $this->get(route('certificates.show', $cert->certificate_hash));

        $response->assertStatus(200);
        $response->assertSee('Certificado Válido y Auténtico');
        $response->assertSee('Audemars Piguet');
        $response->assertSee('SN-TEST-1234');
    }
}
