<?php

namespace Tests\Feature;

use App\Contracts\ExchangeRateProviderInterface;
use App\Models\User;
use App\Models\Watch;
use App\Services\Exchange\ApiExchangeRateProvider;
use App\Services\Exchange\ManualExchangeRateProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://open.er-api.com/v6/latest/COP';

    public function test_exchange_rate_is_cached_until_the_provider_update(): void
    {
        config(['exchange.driver' => 'api']);
        $this->freezeTime();
        Http::preventStrayRequests();
        Http::fakeSequence(self::ENDPOINT)
            ->push($this->responseData(0.00025, 60))
            ->push($this->responseData(0.00026, 86400));

        $provider = app(ExchangeRateProviderInterface::class);

        $this->assertInstanceOf(ApiExchangeRateProvider::class, $provider);
        $this->assertSame('0.00025', (string) $provider->copToUsd()?->value);
        $this->travel(59)->seconds();
        $this->assertSame('0.00025', (string) $provider->copToUsd()?->value);
        Http::assertSentCount(1);

        $this->travel(2)->seconds();
        $this->assertSame('0.00026', (string) $provider->copToUsd()?->value);
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET' && $request->url() === self::ENDPOINT);
    }

    public function test_usd_persists_across_storefront_and_checkout_records_cop(): void
    {
        config(['exchange.driver' => 'api']);
        Http::preventStrayRequests();
        Http::fake([
            self::ENDPOINT => Http::response($this->responseData(0.00025, 86400)),
            'http://127.0.0.1:5001/transactions/new' => Http::response([], 503),
        ]);
        config(['blockchain.primary_node' => 'http://127.0.0.1:5001']);

        $watch = Watch::factory()->create(['price' => 100020, 'stock' => 5]);
        $catalog = route('watch.index', ['q' => $watch->brand]);
        $this->get($catalog)->assertOk()->assertSee('$100.020 COP')->assertDontSee('Rates By Exchange Rate API');
        Http::assertNothingSent();

        $response = $this->from($catalog)->post(route('preferences.update', 'currency'), ['value' => 'USD']);
        $response->assertRedirect($catalog)->assertCookie('currency', 'USD');
        $this->withUnencryptedCookie('currency', $response->getCookie('currency', false)->getValue());

        foreach ([route('home.index'), $catalog, route('watch.show', $watch)] as $url) {
            $this->get($url)->assertOk()->assertSee('$25,01 USD')->assertSee('Rates By Exchange Rate API');
        }

        $user = User::factory()->create();
        $this->actingAs($user)->post(route('wishlist.add', $watch))->assertRedirect();
        $this->get(route('wishlist.index'))->assertOk()->assertSee('$25,01 USD');
        $this->post(route('cart.add', $watch), ['quantity' => 2])->assertRedirect();
        $this->get(route('cart.index'))->assertOk()->assertSee('$50,01 USD');
        Http::assertSentCount(1);

        $this->post(route('cart.checkout'))->assertRedirect();
        $order = $user->orders()->sole();
        $this->get(route('orders.show', $order))->assertOk()->assertSee('$50,01 USD');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'total' => 200040]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'price' => 100020, 'quantity' => 2]);
        $this->assertDatabaseHas('watches', ['id' => $watch->id, 'price' => 100020, 'stock' => 3]);

        $response = $this->from($catalog)->post(route('preferences.update', 'currency'), ['value' => 'COP']);
        $response->assertRedirect($catalog)->assertCookie('currency', 'COP');
        $this->withUnencryptedCookie('currency', $response->getCookie('currency', false)->getValue());
        $this->get($catalog)->assertOk()->assertSee('$100.020 COP')->assertDontSee('Rates By Exchange Rate API');
    }

    public function test_a_connection_failure_falls_back_to_cop_and_is_cached_briefly(): void
    {
        config(['exchange.driver' => 'api']);
        Http::preventStrayRequests();
        $this->freezeTime();
        $attempts = 0;
        Http::fake([self::ENDPOINT => function () use (&$attempts) {
            $attempts++;

            return $attempts === 1 ? Http::failedConnection() : Http::response($this->responseData(0.00025, 86400));
        }]);

        $watch = Watch::factory()->create(['price' => 100000]);
        $url = route('watch.show', $watch);
        $this->withCookie('currency', 'USD');
        $this->get($url)->assertOk()->assertSee('$100.000 COP')
            ->assertSee('La tasa de cambio no está disponible')->assertDontSee('Rates By Exchange Rate API');
        $this->get($url)->assertOk()->assertSee('$100.000 COP');
        $this->assertSame(1, $attempts);

        $this->travel(61)->seconds();
        $this->get($url)->assertOk()->assertSee('$25,00 USD');
        $this->assertSame(2, $attempts);
    }

    public function test_an_expired_or_invalid_payload_is_not_used(): void
    {
        config(['exchange.driver' => 'api']);
        $this->freezeTime();
        Http::preventStrayRequests();
        Http::fakeSequence(self::ENDPOINT)
            ->push($this->responseData(0.00025, 0))
            ->push($this->responseData(0, 86400));

        $provider = app(ExchangeRateProviderInterface::class);
        $this->assertNull($provider->copToUsd());
        $this->travel(61)->seconds();
        $this->assertNull($provider->copToUsd());
        Http::assertSentCount(2);
    }

    public function test_the_same_view_can_use_the_manual_provider_without_http(): void
    {
        Http::preventStrayRequests();
        Http::fake([self::ENDPOINT => Http::response($this->responseData(0.00025, 86400))]);
        $watch = Watch::factory()->create(['price' => 100000]);
        $url = route('watch.show', $watch);
        $this->withCookie('currency', 'USD')->withCookie('locale', 'en');

        config(['exchange.driver' => 'manual', 'exchange.manual_cop_usd_rate' => '0.00030']);
        $this->assertInstanceOf(ManualExchangeRateProvider::class, app(ExchangeRateProviderInterface::class));
        $this->get($url)->assertOk()->assertSee('$30.00 USD')->assertSee('Demo exchange rate.')
            ->assertDontSee('Rates By Exchange Rate API');
        Http::assertNothingSent();

        config(['exchange.driver' => 'api']);
        $this->get($url)->assertOk()->assertSee('$25.00 USD')->assertSee('Rates By Exchange Rate API')
            ->assertDontSee('Demo exchange rate.');
        Http::assertSentCount(1);
    }

    public function test_manual_provider_rejects_invalid_rates_and_production_use(): void
    {
        config(['exchange.driver' => 'manual', 'exchange.manual_cop_usd_rate' => 'invalid']);
        $provider = app(ExchangeRateProviderInterface::class);
        $this->assertNull($provider->copToUsd());

        config(['app.env' => 'production', 'exchange.manual_cop_usd_rate' => '0.00030']);
        $this->assertNull($provider->copToUsd());
    }

    private function responseData(float $rate, int $secondsUntilUpdate): array
    {
        return [
            'result' => 'success',
            'base_code' => 'COP',
            'rates' => ['USD' => $rate],
            'time_last_update_unix' => now()->subHour()->timestamp,
            'time_next_update_unix' => now()->addSeconds($secondsUntilUpdate)->timestamp,
        ];
    }
}
