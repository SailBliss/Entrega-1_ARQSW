<?php

// Nicolas Ortiz

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Watch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWatchTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Reloj Nuevo',
            'brand' => 'Casio',
            'description' => 'Descripción de prueba',
            'price' => 150000,
            'stock' => 7,
            'image' => null,
        ], $overrides);
    }

    public function test_is_admin_es_falso_por_defecto_y_no_es_asignable_en_masa(): void
    {
        $user = User::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'secreto123', 'is_admin' => true]);

        $this->assertFalse($user->fresh()->isAdmin());
        $this->assertTrue(User::factory()->admin()->create()->isAdmin());
    }

    public function test_visitante_no_puede_entrar_al_admin(): void
    {
        $watch = Watch::factory()->create();

        $this->get('/admin')->assertRedirect('/login');
        $this->get('/admin/watches')->assertRedirect('/login');
        $this->get('/admin/watches/create')->assertRedirect('/login');
        $this->post('/admin/watches', $this->validData())->assertRedirect('/login');
        $this->get('/admin/watches/'.$watch->id.'/edit')->assertRedirect('/login');
        $this->put('/admin/watches/'.$watch->id, $this->validData())->assertRedirect('/login');
        $this->delete('/admin/watches/'.$watch->id)->assertRedirect('/login');

        $this->assertDatabaseCount('watches', 1);
    }

    public function test_usuario_normal_no_puede_entrar_al_admin(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/watches')->assertForbidden();
        $this->actingAs($user)->get('/admin/watches/create')->assertForbidden();
        $this->actingAs($user)->post('/admin/watches', $this->validData())->assertForbidden();
        $this->actingAs($user)->put('/admin/watches/'.$watch->id, $this->validData())->assertForbidden();
        $this->actingAs($user)->delete('/admin/watches/'.$watch->id)->assertForbidden();

        $this->assertDatabaseCount('watches', 1);
        $this->assertDatabaseMissing('watches', ['name' => 'Reloj Nuevo']);
    }

    public function test_administrador_puede_entrar_al_panel(): void
    {
        $admin = User::factory()->admin()->create();
        Watch::factory()->create(['name' => 'Visible En Panel']);

        $this->actingAs($admin)->get('/admin')->assertRedirect('/admin/watches');
        $this->actingAs($admin)->get('/admin/watches')->assertOk()->assertSee('Visible En Panel')->assertSee('Panel de administración');
        $this->actingAs($admin)->get('/admin/watches/create')->assertOk();
    }

    public function test_administrador_puede_crear_reloj(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/watches', $this->validData())
            ->assertRedirect('/admin/watches')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('watches', ['name' => 'Reloj Nuevo', 'brand' => 'Casio', 'price' => 150000, 'stock' => 7]);
    }

    public function test_crear_reloj_valida_los_datos(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/watches', $this->validData(['name' => '', 'price' => 0, 'stock' => -1]))
            ->assertSessionHasErrors(['name', 'price', 'stock']);

        $this->assertDatabaseCount('watches', 0);
    }

    public function test_administrador_puede_editar_reloj(): void
    {
        $admin = User::factory()->admin()->create();
        $watch = Watch::factory()->create(['name' => 'Antiguo', 'price' => 100000]);

        $this->actingAs($admin)->get('/admin/watches/'.$watch->id.'/edit')->assertOk()->assertSee('Antiguo');

        $this->actingAs($admin)->put('/admin/watches/'.$watch->id, $this->validData(['name' => 'Modificado', 'price' => 222000]))
            ->assertRedirect('/admin/watches')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('watches', ['id' => $watch->id, 'name' => 'Modificado', 'price' => 222000]);
        $this->assertDatabaseMissing('watches', ['name' => 'Antiguo']);
    }

    public function test_administrador_puede_eliminar_reloj(): void
    {
        $admin = User::factory()->admin()->create();
        $watch = Watch::factory()->create();

        $this->actingAs($admin)->delete('/admin/watches/'.$watch->id)
            ->assertRedirect('/admin/watches')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('watches', ['id' => $watch->id]);
    }

    public function test_no_se_puede_eliminar_un_reloj_con_pedidos(): void
    {
        $admin = User::factory()->admin()->create();
        $watch = Watch::factory()->create();
        $order = Order::create(['user_id' => $admin->id, 'total' => $watch->price]);
        $order->items()->create(['watch_id' => $watch->id, 'quantity' => 1, 'price' => $watch->price]);

        $this->actingAs($admin)->delete('/admin/watches/'.$watch->id)->assertSessionHasErrors('watch');

        $this->assertDatabaseHas('watches', ['id' => $watch->id]);
    }
}
