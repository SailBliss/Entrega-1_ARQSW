<?php

namespace App\Services\Exchange;

use App\Contracts\ExchangeRateProviderInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use InvalidArgumentException;

final class ApiExchangeRateProvider implements ExchangeRateProviderInterface
{
    private const CACHE_KEY = 'exchange:cop-usd:v1';

    public function __construct(private Factory $http, private CacheManager $cache) {}

    public function copToUsd(): ?ExchangeRate
    {
        try {
            $value = $this->cache->get(self::CACHE_KEY);

            if ($value === null) {
                $value = $this->cache->lock(self::CACHE_KEY.':lock', 10)->block(4, function () {
                    return $this->cache->get(self::CACHE_KEY) ?? $this->fetch();
                });
            }

            return is_string($value) ? new ExchangeRate($value) : null;
        } catch (LockTimeoutException|InvalidArgumentException) {
            return null;
        }
    }

    private function fetch(): string|false
    {
        $value = false;
        $ttl = 60;

        try {
            $response = $this->http->acceptJson()->connectTimeout(2)->timeout(3)
                ->get('https://open.er-api.com/v6/latest/COP');

            if ($response->status() === 429) {
                $ttl = 1200;
            }

            $data = $response->json();
            $now = now()->timestamp;

            if ($response->successful() && is_array($data)
                && ($data['result'] ?? null) === 'success'
                && ($data['base_code'] ?? null) === 'COP'
                && is_numeric($data['rates']['USD'] ?? null)
                && is_int($data['time_last_update_unix'] ?? null)
                && is_int($data['time_next_update_unix'] ?? null)
                && $data['time_last_update_unix'] <= $now
                && $data['time_next_update_unix'] > $now) {
                $rate = new ExchangeRate((string) $data['rates']['USD']);
                $value = (string) $rate->value;
                $ttl = min(86400, $data['time_next_update_unix'] - $now);
            }
        } catch (ConnectionException|InvalidArgumentException) {
            $value = false;
        }

        $this->cache->put(self::CACHE_KEY, $value, $ttl);

        return $value;
    }
}
