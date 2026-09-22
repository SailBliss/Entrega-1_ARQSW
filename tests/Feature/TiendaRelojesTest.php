<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Watch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiendaRelojesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrarse(): void
    {
        $this->post('/register', [
            'name' => 'Ana', 'email' => 'ana@example.com',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
    }

    public function test_registro_valida_datos(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $this->post('/register', [
            'name' => '', 'email' => 'ana@example.com',
            'password' => 'corta', 'password_confirmation' => 'otra',
        ])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_iniciar_y_cerrar_sesion(): void
    {
        User::factory()->create(['email' => 'ana@example.com', 'password' => 'secreto123']);

        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'mala'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'secreto123'])
            ->assertRedirect('/');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_listar_ver_detalle_y_buscar_relojes(): void
    {
        $a = Watch::factory()->create(['name' => 'Alfa Uno', 'brand' => 'Seiko']);
        $b = Watch::factory()->create(['name' => 'Beta Dos', 'brand' => 'Casio']);

        $this->get('/watches')->assertOk()->assertSee('Alfa Uno')->assertSee('Beta Dos');
        $this->get('/watches/'.$a->id)->assertOk()->assertSee('Alfa Uno');
        $this->get('/watches/999999')->assertNotFound();

        $this->get('/watches?q=casio')->assertOk()->assertSee('Beta Dos')->assertDontSee('Alfa Uno');
        $this->get('/watches?q=zzzz')->assertOk()->assertSee('No se encontraron');
    }

    public function test_rutas_protegidas_requieren_login(): void
    {
        foreach (['/cart', '/wishlist'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $watch = Watch::factory()->create();
        $this->post('/cart/add/'.$watch->id)->assertRedirect('/login');
        $this->post('/wishlist/add/'.$watch->id)->assertRedirect('/login');
    }

    public function test_carrito_anadir_quitar_ver_vaciar_y_total(): void
    {
        $user = User::factory()->create();
        $a = Watch::factory()->create(['price' => 100000, 'stock' => 5]);
        $b = Watch::factory()->create(['price' => 250000, 'stock' => 5]);

        $this->actingAs($user)->post('/cart/add/'.$a->id, ['quantity' => 2])->assertSessionHas('success');
        $this->actingAs($user)->post('/cart/add/'.$b->id)->assertSessionHas('success');
        $this->actingAs($user)->post('/cart/add/'.$a->id)->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', ['user_id' => $user->id, 'watch_id' => $a->id, 'quantity' => 3]);

        $this->actingAs($user)->get('/cart')->assertOk()->assertSee('$550.000');

        $this->actingAs($user)->delete('/cart/remove/'.$b->id);
        $this->assertDatabaseMissing('cart_items', ['watch_id' => $b->id]);
        $this->actingAs($user)->get('/cart')->assertSee('$300.000');

        $this->actingAs($user)->delete('/cart');
        $this->assertDatabaseCount('cart_items', 0);
        $this->actingAs($user)->get('/cart')->assertSee('Tu carrito está vacío');
    }

    public function test_carrito_no_supera_el_stock_ni_agrega_agotados(): void
    {
        $user = User::factory()->create();
        $w = Watch::factory()->create(['stock' => 2]);
        $sold = Watch::factory()->create(['stock' => 0]);

        $this->actingAs($user)->post('/cart/add/'.$w->id, ['quantity' => 10]);
        $this->assertDatabaseHas('cart_items', ['watch_id' => $w->id, 'quantity' => 2]);

        $this->actingAs($user)->post('/cart/add/'.$sold->id)->assertSessionHasErrors('cart');
        $this->assertDatabaseMissing('cart_items', ['watch_id' => $sold->id]);
    }

    public function test_comprar_crea_pedido_descuenta_stock_y_vacia_carrito(): void
    {
        $user = User::factory()->create();
        $w = Watch::factory()->create(['price' => 200000, 'stock' => 5]);

        $this->actingAs($user)->post('/cart/add/'.$w->id, ['quantity' => 2]);
        $response = $this->actingAs($user)->post('/cart/checkout');

        $order = $user->orders()->first();
        $response->assertRedirect('/orders/'.$order->id);
        $this->assertSame(400000, (int) $order->total);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'watch_id' => $w->id, 'quantity' => 2, 'price' => 200000]);
        $this->assertSame(3, $w->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($user)->get('/orders/'.$order->id)->assertOk()->assertSee('$400.000');
        $this->actingAs(User::factory()->create())->get('/orders/'.$order->id)->assertNotFound();
    }

    public function test_comprar_con_carrito_vacio_o_sin_stock_falla(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/cart/checkout')->assertSessionHasErrors('cart');

        $w = Watch::factory()->create(['stock' => 3]);
        $this->actingAs($user)->post('/cart/add/'.$w->id, ['quantity' => 3]);
        $w->update(['stock' => 1]);

        $this->actingAs($user)->post('/cart/checkout')->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $w->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_lista_de_deseados(): void
    {
        $user = User::factory()->create();
        $w = Watch::factory()->create(['name' => 'Deseado Raro']);

        $this->actingAs($user)->post('/wishlist/add/'.$w->id);
        $this->actingAs($user)->post('/wishlist/add/'.$w->id);
        $this->assertDatabaseCount('wishlist_items', 1);

        $this->actingAs($user)->get('/wishlist')->assertOk()->assertSee('Deseado Raro');

        $this->actingAs($user)->delete('/wishlist/remove/'.$w->id);
        $this->assertDatabaseCount('wishlist_items', 0);
        $this->actingAs($user)->get('/wishlist')->assertSee('vacía');
    }

    public function test_cada_usuario_ve_solo_su_carrito_y_deseados(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $w = Watch::factory()->create(['name' => 'Solo Del Uno']);

        $this->actingAs($u1)->post('/cart/add/'.$w->id);
        $this->actingAs($u1)->post('/wishlist/add/'.$w->id);

        $this->actingAs($u2)->get('/cart')->assertDontSee('Solo Del Uno');
        $this->actingAs($u2)->get('/wishlist')->assertDontSee('Solo Del Uno');
    }
}
