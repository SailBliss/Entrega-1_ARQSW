<?php

namespace App\Contracts;

use App\Services\Exchange\ExchangeRate;

interface ExchangeRateProviderInterface
{
    public function copToUsd(): ?ExchangeRate;
}
