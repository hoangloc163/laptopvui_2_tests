<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @source Test case TC-UI-09 (đơn vị tiền VNĐ)
 */
#[Group('Unit - Định dạng giá')]
class PriceFormatTest extends TestCase
{
    #[Test]
    #[DataProvider('priceFormatProvider')]
    public function it_formats_price_vietnamese_style(int $amount, string $expected): void
    {
        // Mimics: number_format($amount, 0, ',', '.') . ' ₫' used in views
        $formatted = number_format($amount, 0, ',', '.') . ' ₫';
        $this->assertSame($expected, $formatted);
    }

    public static function priceFormatProvider(): array
    {
        return [
            'zero' => [0, '0 ₫'],
            'hundred' => [100, '100 ₫'],
            'thousand' => [1_000, '1.000 ₫'],
            'ten_thousand' => [10_000, '10.000 ₫'],
            'million' => [1_000_000, '1.000.000 ₫'],
            'seventeen_million' => [17_490_000, '17.490.000 ₫'],
            'large' => [999_999_999, '999.999.999 ₫'],
        ];
    }

    #[Test]
    public function it_calculates_discount_percentage(): void
    {
        $gia = 20_000_000;
        $gia_km = 17_000_000;
        $discount = round(($gia - $gia_km) / $gia * 100);
        $this->assertSame(15.0, $discount);
    }

    #[Test]
    public function it_calculates_cart_total(): void
    {
        $cart = [
            ['gia' => 17_490_000, 'soluong' => 2],
            ['gia' => 26_490_000, 'soluong' => 1],
        ];
        $total = array_sum(array_map(fn($i) => $i['gia'] * $i['soluong'], $cart));
        $this->assertSame(61_470_000, $total);
    }
}
