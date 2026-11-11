<?php

namespace App\Services\Exchange;

use Brick\Math\BigDecimal;
use InvalidArgumentException;

final readonly class ExchangeRate
{
    public BigDecimal $value;

    public function __construct(string $value, public bool $demo = false)
    {
        if (strlen($value) > 40 || ! preg_match('/^\d+(?:\.\d+)?(?:[eE][+-]?\d{1,2})?$/D', $value)) {
            throw new InvalidArgumentException('Invalid COP/USD rate.');
        }

        $this->value = BigDecimal::of($value);

        if ($this->value->isLessThanOrEqualTo(0) || $this->value->isGreaterThanOrEqualTo(1)) {
            throw new InvalidArgumentException('COP/USD rate must be between zero and one.');
        }
    }
}
