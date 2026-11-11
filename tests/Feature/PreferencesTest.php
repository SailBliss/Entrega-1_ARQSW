<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Watch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_persists_across_pages_and_can_return_to_spanish(): void
    {
        $catalog = route('watch.index', ['q' => 'Seiko']);
        $this->get($catalog)->assertOk()->assertSee('<html lang="es">', false)->assertSee('Inicio');

        $response = $this->from($catalog)->post(route('preferences.update', 'locale'), ['value' => 'en']);
        $response->assertRedirect($catalog)->assertCookie('locale', 'en');
        $this->withUnencryptedCookie('locale', $response->getCookie('locale', false)->getValue());

        $this->get($catalog)->assertOk()->assertSee('<html lang="en">', false)->assertSee('Results for "Seiko"');
        $this->get(route('login'))->assertOk()->assertSee('Welcome back');
        $this->get(route('register'))->assertOk()->assertSee('Confirm password');

        $response = $this->from($catalog)->post(route('preferences.update', 'locale'), ['value' => 'es']);
        $response->assertRedirect($catalog)->assertCookie('locale', 'es');
        $this->withUnencryptedCookie('locale', $response->getCookie('locale', false)->getValue());

        $this->get(route('login'))->assertOk()->assertSee('<html lang="es">', false)->assertSee('Bienvenido de nuevo');
    }

    public function test_validation_and_application_errors_follow_the_chosen_language(): void
    {
        $this->post(route('register.store'))->assertSessionHasErrors(['name' => 'El campo nombre es obligatorio.']);

        $this->withCookie('locale', 'en')->post(route('register.store'))
            ->assertSessionHasErrors(['name' => 'The name field is required.']);

        $this->post(route('login.store'), ['email' => 'guest@example.com', 'password' => 'invalid'])
            ->assertSessionHasErrors(['email' => 'Incorrect email or password.']);

        $this->actingAs(User::factory()->create())->get(route('admin.watches.index'))
            ->assertForbidden()->assertSee('You do not have permission to access this section.');

        $this->withCookie('locale', 'es')->get(route('watch.show', 999999))
            ->assertNotFound()->assertSee('Página no encontrada');
    }

    public function test_invalid_preferences_do_not_replace_existing_cookies(): void
    {
        $this->withCookie('locale', 'en')->withCookie('currency', 'COP');

        foreach (['locale' => 'fr', 'currency' => 'EUR'] as $preference => $value) {
            $this->from(route('watch.index'))->post(route('preferences.update', $preference), ['value' => $value])
                ->assertRedirect(route('watch.index'))->assertSessionHasErrors('value')->assertCookieMissing($preference);
        }

        $this->get(route('watch.index'))->assertOk()->assertSee('<html lang="en">', false);
    }

    public function test_admin_panel_uses_the_preferred_language_and_keeps_prices_in_cop(): void
    {
        Http::preventStrayRequests();
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        Watch::factory()->create(['price' => 100000]);

        $this->actingAs($admin)->withCookie('locale', 'en')->withCookie('currency', 'USD')
            ->get(route('admin.watches.index'))->assertOk()->assertSee('Admin panel')->assertSee('$100,000 COP')
            ->assertSee(route('preferences.update', 'locale'))->assertDontSee(route('preferences.update', 'currency'));
    }

    public function test_all_store_message_keys_and_placeholders_exist_in_both_languages(): void
    {
        $spanish = require resource_path('lang/es/messages.php');
        $english = require resource_path('lang/en/messages.php');

        $this->assertSame(array_keys($spanish), array_keys($english));

        foreach ($spanish as $key => $message) {
            preg_match_all('/:[a-z]+/', $message, $spanishParameters);
            preg_match_all('/:[a-z]+/', $english[$key], $englishParameters);
            $this->assertEqualsCanonicalizing($spanishParameters[0], $englishParameters[0], $key);
        }
    }

    public function test_certificate_labels_are_localized_without_changing_verification(): void
    {
        $user = User::factory()->create();
        $watch = Watch::factory()->create();
        $order = $user->orders()->create(['total' => $watch->price]);
        $item = $order->items()->create(['watch_id' => $watch->id, 'quantity' => 1, 'price' => $watch->price]);
        $item->certificate()->create([
            'order_id' => $order->id,
            'watch_id' => $watch->id,
            'user_id' => $user->id,
            'serial_number' => 'SAMPLE-01',
            'certificate_hash' => 'sample-hash',
        ]);

        config(['blockchain.nodes' => ['store' => 'http://127.0.0.1:5001']]);
        Http::preventStrayRequests();
        Http::fake([
            'http://127.0.0.1:5001/verify/sample-hash' => Http::response(['valid' => true, 'block_hash' => 'sample-block']),
        ]);

        $url = route('certificates.show', 'sample-hash');
        $this->get($url)->assertOk()->assertSee('Certificado Válido y Auténtico')->assertSee('Imprimir Certificado');
        $this->withCookie('locale', 'en')->get($url)->assertOk()
            ->assertSee('Valid and Authentic Certificate')->assertSee('Print certificate')->assertSee('sample-block');
        Http::assertSentCount(2);
    }
}
