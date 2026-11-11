<?php

namespace Tests\Unit;

use App\Contracts\ExchangeRateProviderInterface;
use App\Services\Exchange\ExchangeRate;
use App\Services\PriceFormatter;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

final class PriceFormatterTest extends TestCase
{
    public function test_cop_format_preserves_large_amounts_without_loading_a_rate(): void
    {
        $request = Request::create('/');
        $request->setLocale('es');
        $provider = $this->createMock(ExchangeRateProviderInterface::class);
        $provider->expects($this->never())->method('copToUsd');
        $formatter = new PriceFormatter($provider, $request);

        $this->assertSame('$9.007.199.254.740.993 COP', $formatter->format('9007199254740993'));
        $this->assertNull($formatter->notice());
    }

    public function test_usd_rounds_each_display_amount_only_after_conversion(): void
    {
        $request = Request::create('/', cookies: ['currency' => 'USD']);
        $request->setLocale('en');
        $provider = $this->createMock(ExchangeRateProviderInterface::class);
        $provider->expects($this->once())->method('copToUsd')->willReturn(new ExchangeRate('0.00025'));
        $formatter = new PriceFormatter($provider, $request);

        $this->assertSame('$0.01 USD', $formatter->format(20));
        $this->assertSame('$0.01 USD', $formatter->format(20, 2));
        $this->assertSame('api', $formatter->notice());
    }
}
