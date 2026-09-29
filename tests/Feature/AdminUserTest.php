<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_o_usuario_no_admin_no_puede_gestionar_usuarios(): void
    {
        $user = User::factory()->create();

        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/users/create')->assertRedirect('/login');

        // Usuario autenticado pero no admin recibe 403
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->get('/admin/users/create')->assertForbidden();
    }

    public function test_admin_user_index_lists_users(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Boss']);
        $u1 = User::factory()->create(['name' => 'Usuario Uno', 'email' => 'uno@example.com']);
        $u2 = User::factory()->create(['name' => 'Usuario Dos', 'email' => 'dos@example.com']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Usuario Uno');
        $response->assertSee('Usuario Dos');
        $response->assertSee('uno@example.com');
        $response->assertSee('dos@example.com');
    }

    public function test_admin_user_create_view_loads(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertOk();
        $response->assertSee('admin/users');
    }

    public function test_admin_user_store_creates_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Carlos Administrado',
            'email' => 'carlos@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => 1,
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'carlos@example.com',
            'name' => 'Carlos Administrado',
        ]);

        $created = User::where('email', 'carlos@example.com')->first();
        $this->assertTrue(Hash::check('password123', $created->password));
        $this->assertTrue($created->isAdmin());
    }

    public function test_admin_user_store_validates_required_and_unique_fields(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'existente@example.com']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => '',
            'email' => 'existente@example.com',
            'password' => 'corta',
            'password_confirmation' => 'distinta',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_admin_user_edit_view_loads_with_user_data(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Maria Lopez', 'email' => 'maria@example.com']);

        $response = $this->actingAs($admin)->get('/admin/users/'.$user->id.'/edit');

        $response->assertOk();
        $response->assertSee('Maria Lopez');
        $response->assertSee('maria@example.com');
    }

    public function test_admin_user_update_modifies_data_without_password_change(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => 'claveOriginal123',
        ]);

        $response = $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'name' => 'Nombre Cambiado',
            'email' => 'cambiado@example.com',
            'password' => '',
            'password_confirmation' => '',
            'is_admin' => 0,
        ]);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Nombre Cambiado', $user->name);
        $this->assertSame('cambiado@example.com', $user->email);
        $this->assertTrue(Hash::check('claveOriginal123', $user->password));
    }

    public function test_admin_user_update_with_new_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create([
            'password' => 'claveVieja123',
        ]);

        $response = $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'claveNueva456',
            'password_confirmation' => 'claveNueva456',
        ]);

        $response->assertRedirect('/admin/users');
        $user->refresh();
        $this->assertTrue(Hash::check('claveNueva456', $user->password));
    }

    public function test_admin_user_destroy_deletes_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete('/admin/users/'.$user->id);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
