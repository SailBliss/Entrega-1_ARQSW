<?php

namespace App\Services\Exchange;

use App\Contracts\ExchangeRateProviderInterface;
use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;

final class ManualExchangeRateProvider implements ExchangeRateProviderInterface
{
    public function __construct(private Repository $config) {}

    public function copToUsd(): ?ExchangeRate
    {
        if ($this->config->get('app.env') === 'production') {
            return null;
        }

        try {
            return new ExchangeRate((string) $this->config->get('exchange.manual_cop_usd_rate'), demo: true);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}
