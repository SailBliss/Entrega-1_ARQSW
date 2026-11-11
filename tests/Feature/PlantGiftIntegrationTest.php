<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Models\Watch;
use App\Services\PlantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlantGiftIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected array $mockPlantsResponse = [
        'data' => [
            [
                'id' => 1,
                'nombre' => 'Monstera deliciosa',
                'categoria' => 'Interior',
                'descripcion' => 'Hojas grandes',
                'precio' => 65000,
                'stock' => 5,
                'imagen_url' => 'https://example.com/monstera.jpg',
                'detalle_url' => 'http://34.136.112.77/catalogo/1',
            ],
            [
                'id' => 2,
                'nombre' => 'Poto dorado',
                'categoria' => 'Interior',
                'descripcion' => 'Colgante',
                'precio' => 22000,
                'stock' => 0, // No stock -> should be excluded
                'imagen_url' => 'https://example.com/poto.jpg',
                'detalle_url' => 'http://34.136.112.77/catalogo/2',
            ],
            [
                'id' => 3,
                'nombre' => 'Lavanda',
                'categoria' => 'Aromáticas',
                'descripcion' => 'Relajante',
                'precio' => 25000,
                'stock' => 10,
                'imagen_url' => 'https://example.com/lavanda.jpg',
                'detalle_url' => 'http://34.136.112.77/catalogo/3',
            ],
        ],
        'meta' => [
            'total' => 3,
            'proveedor' => 'El Rincón de las Plantas',
        ],
    ];

    public function test_plant_service_filters_plants_with_stock(): void
    {
        Http::fake([
            'http://34.136.112.77/api/plantas-disponibles' => Http::response($this->mockPlantsResponse, 200),
        ]);

        $service = new PlantService;
        $plants = $service->getAvailablePlants();

        $this->assertCount(2, $plants); // id 1 and id 3 (id 2 had stock 0)
        $this->assertEquals('Monstera deliciosa', $plants[0]['nombre']);
        $this->assertEquals('Lavanda', $plants[1]['nombre']);
    }

    public function test_cart_with_one_watch_does_not_get_gift_plant(): void
    {
        Http::fake([
            'http://34.136.112.77/api/plantas-disponibles' => Http::response($this->mockPlantsResponse, 200),
        ]);

        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'Speedmaster',
            'brand' => 'Omega',
            'description' => 'Moonwatch',
            'price' => 5000,
            'stock' => 10,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'watch_id' => $watch->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('¡Añade 1 reloj más a tu carrito y te regalamos una planta');
        $response->assertDontSee('GRATIS');
    }

    public function test_cart_with_two_or_more_watches_gets_gift_plant(): void
    {
        Http::fake([
            'http://34.136.112.77/api/plantas-disponibles' => Http::response($this->mockPlantsResponse, 200),
        ]);

        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'Submariner',
            'brand' => 'Rolex',
            'description' => 'Diver',
            'price' => 12000,
            'stock' => 10,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'watch_id' => $watch->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('¡Calificas para una planta de regalo!');
        $response->assertSee('Regalo promocional');
        $response->assertSee('Gratis');
    }

    public function test_checkout_with_two_watches_persists_gift_plant_in_order(): void
    {
        Http::fake([
            'http://34.136.112.77/api/plantas-disponibles' => Http::response($this->mockPlantsResponse, 200),
            'http://127.0.0.1:5001/transactions/new' => Http::response(['message' => 'ok'], 201),
            'http://127.0.0.1:5001/mine' => Http::response(['message' => 'ok', 'block' => ['index' => 1, 'hash' => '000abc']], 200),
        ]);

        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'PRX',
            'brand' => 'Tissot',
            'description' => 'Powermatic',
            'price' => 700,
            'stock' => 10,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'watch_id' => $watch->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('cart.checkout'));

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 1400,
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order->gift_plant_name);
        $this->assertTrue($order->hasGiftPlant());

        // Visit order detail view and ensure gift plant is rendered
        $orderView = $this->actingAs($user)->get(route('orders.show', $order));
        $orderView->assertStatus(200);
        $orderView->assertSee('Planta de Obsequio Incluida en tu Pedido');
        $orderView->assertSee($order->gift_plant_name);
    }

    public function test_graceful_degradation_when_plants_api_is_unreachable(): void
    {
        Http::fake([
            'http://34.136.112.77/api/plantas-disponibles' => Http::response(null, 500),
            'http://127.0.0.1:5001/transactions/new' => Http::response(['message' => 'ok'], 201),
            'http://127.0.0.1:5001/mine' => Http::response(['message' => 'ok', 'block' => ['index' => 1, 'hash' => '000abc']], 200),
        ]);

        $user = User::factory()->create();
        $watch = Watch::create([
            'name' => 'G-Shock',
            'brand' => 'Casio',
            'description' => 'Classic',
            'price' => 100,
            'stock' => 5,
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'watch_id' => $watch->id,
            'quantity' => 3,
        ]);

        // Purchase should still succeed without crashing
        $response = $this->actingAs($user)->post(route('cart.checkout'));
        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 300,
            'gift_plant_name' => null,
        ]);
    }
}
