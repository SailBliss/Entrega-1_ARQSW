<?php

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\Services\Exchange\ExchangeRate;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Http\Request;

final class PriceFormatter
{
    private const RATE_ATTRIBUTE = 'exchange.cop_usd';

    public function __construct(private ExchangeRateProviderInterface $provider, private Request $request) {}

    public function currency(): string
    {
        return $this->request->cookie('currency') === 'USD' ? 'USD' : 'COP';
    }

    public function format(int|string $cop, int $quantity = 1): string
    {
        $amount = BigDecimal::of($cop)->multipliedBy($quantity);

        if ($this->currency() === 'USD') {
            $rate = $this->rate();

            if ($rate !== null) {
                return $this->formatAmount($amount->multipliedBy($rate->value), 'USD', 2);
            }
        }

        return $this->formatAmount($amount, 'COP', 0);
    }

    public function formatCop(int|string $cop): string
    {
        return $this->formatAmount(BigDecimal::of($cop), 'COP', 0);
    }

    public function notice(): ?string
    {
        if (! $this->request->attributes->has(self::RATE_ATTRIBUTE)) {
            return null;
        }

        $rate = $this->rate();

        return match (true) {
            $rate === null => 'unavailable',
            $rate->demo => 'demo',
            default => 'api',
        };
    }

    private function rate(): ?ExchangeRate
    {
        if (! $this->request->attributes->has(self::RATE_ATTRIBUTE)) {
            $this->request->attributes->set(self::RATE_ATTRIBUTE, $this->provider->copToUsd());
        }

        return $this->request->attributes->get(self::RATE_ATTRIBUTE);
    }

    private function formatAmount(BigDecimal $amount, string $currency, int $scale): string
    {
        $parts = explode('.', (string) $amount->toScale($scale, RoundingMode::HalfUp));
        $spanish = $this->request->getLocale() === 'es';
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', $spanish ? '.' : ',', $parts[0]);
        $fraction = $scale > 0 ? ($spanish ? ',' : '.').$parts[1] : '';

        return '$'.$whole.$fraction.' '.$currency;
    }
}
