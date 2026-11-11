<?php

namespace App\Providers;

use App\Contracts\ExchangeRateProviderInterface;
use App\Services\Exchange\ApiExchangeRateProvider;
use App\Services\Exchange\ManualExchangeRateProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class ExchangeRateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExchangeRateProviderInterface::class, function ($app) {
            return match ($app['config']->get('exchange.driver')) {
                'api' => $app->make(ApiExchangeRateProvider::class),
                'manual' => $app->make(ManualExchangeRateProvider::class),
                default => throw new InvalidArgumentException('Unknown exchange rate driver.'),
            };
        });
    }
}
