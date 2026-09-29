<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_index_lists_users(): void
    {
        $u1 = User::factory()->create(['name' => 'Usuario Uno', 'email' => 'uno@example.com']);
        $u2 = User::factory()->create(['name' => 'Usuario Dos', 'email' => 'dos@example.com']);

        $response = $this->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Usuario Uno');
        $response->assertSee('Usuario Dos');
        $response->assertSee('uno@example.com');
        $response->assertSee('dos@example.com');
    }

    public function test_admin_user_create_view_loads(): void
    {
        $response = $this->get('/admin/users/create');

        $response->assertOk();
        $response->assertSee('admin/users');
    }

    public function test_admin_user_store_creates_user(): void
    {
        $response = $this->post('/admin/users', [
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
    }

    public function test_admin_user_store_validates_required_and_unique_fields(): void
    {
        User::factory()->create(['email' => 'existente@example.com']);

        $response = $this->post('/admin/users', [
            'name' => '',
            'email' => 'existente@example.com',
            'password' => 'corta',
            'password_confirmation' => 'distinta',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_admin_user_edit_view_loads_with_user_data(): void
    {
        $user = User::factory()->create(['name' => 'Maria Lopez', 'email' => 'maria@example.com']);

        $response = $this->get('/admin/users/'.$user->id.'/edit');

        $response->assertOk();
        $response->assertSee('Maria Lopez');
        $response->assertSee('maria@example.com');
    }

    public function test_admin_user_update_modifies_data_without_password_change(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => 'claveOriginal123',
        ]);

        $response = $this->put('/admin/users/'.$user->id, [
            'name' => 'Nombre Cambiado',
            'email' => 'cambiado@example.com',
            'password' => '',
            'password_confirmation' => '',
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
        $user = User::factory()->create([
            'password' => 'claveVieja123',
        ]);

        $response = $this->put('/admin/users/'.$user->id, [
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
        $user = User::factory()->create();

        $response = $this->delete('/admin/users/'.$user->id);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
