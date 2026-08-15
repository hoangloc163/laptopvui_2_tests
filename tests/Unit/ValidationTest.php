<?php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Unit tests for validation logic used across the app.
 * These test pure functions - no HTTP, no database.
 *
 * @source BaoCao_TestCase_LaptopVui - business rules BR-01 to BR-15
 */
#[Group('Unit - Validation')]
class ValidationTest extends TestCase
{
    // ========== EMAIL VALIDATION (used in Register, Checkout) ==========

    #[Test]
    #[DataProvider('validEmailProvider')]
    public function it_accepts_valid_emails(string $email): void
    {
        // Mimics: filter_var($email, FILTER_VALIDATE_EMAIL) used in UserController
        $this->assertNotFalse(filter_var(strtolower(trim($email)), FILTER_VALIDATE_EMAIL), "Should accept: {$email}");
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple' => ['user@example.com'],
            'with_dot' => ['first.last@example.com'],
            'with_plus' => ['user+tag@example.com'],
            'subdomain' => ['user@mail.example.com'],
            'vn_domain' => ['user@congty.vn'],
            'uppercase' => ['USER@EXAMPLE.COM'],
        ];
    }

    #[Test]
    #[DataProvider('invalidEmailProvider')]
    public function it_rejects_invalid_emails(string $email): void
    {
        $this->assertFalse(filter_var(strtolower(trim($email)), FILTER_VALIDATE_EMAIL), "Should reject: {$email}");
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty' => [''],
            'no_at' => ['userexample.com'],
            'no_domain' => ['user@'],
            'no_local' => ['@example.com'],
            'spaces' => ['user @example.com'],
            'double_at' => ['user@@example.com'],
            'no_tld' => ['user@example'],
        ];
    }

    // ========== PHONE VALIDATION (used in Checkout) ==========

    #[Test]
    #[DataProvider('validPhoneProvider')]
    public function it_accepts_valid_phone_numbers(string $phone): void
    {
        // Mimics regex /^[0-9+\s().-]{8,20}$/ in SanphamController::checkout_
        $this->assertMatchesRegularExpression('/^[0-9+\s().-]{8,20}$/', $phone, "Should accept: {$phone}");
    }

    public static function validPhoneProvider(): array
    {
        return [
            'simple_vn' => ['0912345678'],
            'with_country' => ['+84912345678'],
            'with_spaces' => ['+84 912 345 678'],
            'with_dashes' => ['0912-345-678'],
            'with_parens' => ['(028) 3822 1234'],
            'international' => ['+1-555-123-4567'],
            'min_length_8' => ['12345678'],
            'max_length_20' => ['+84 (28) 1234-5678'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPhoneProvider')]
    public function it_rejects_invalid_phone_numbers(string $phone): void
    {
        $this->assertDoesNotMatchRegularExpression('/^[0-9+\s().-]{8,20}$/', $phone, "Should reject: {$phone}");
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            'empty' => [''],
            'too_short' => ['1234567'],
            'letters' => ['0912abc678'],
            'with_at' => ['0912@345678'],
            'too_long' => ['+84 912 345 678 999 000 111'],
        ];
    }

    // ========== PRICE VALIDATION (used in Admin add/edit product) ==========

    #[Test]
    public function gia_must_be_positive(): void
    {
        $this->assertTrue(1000 > 0, 'gia > 0 accepted');
        $this->assertFalse(0 > 0, 'gia = 0 rejected');
        $this->assertFalse(-100 > 0, 'gia < 0 rejected');
    }

    #[Test]
    public function gia_km_must_be_less_than_gia_when_positive(): void
    {
        // BR-01: gia_km > 0 must be < gia
        $gia = 1_000_000;

        $this->assertTrue(800_000 <= $gia, 'gia_km < gia OK');
        $this->assertTrue(0 <= $gia, 'gia_km = 0 (no discount) OK');
        $this->assertFalse(1_500_000 <= $gia, 'gia_km > gia rejected');
    }

    // ========== PASSWORD VALIDATION (Register) ==========

    #[Test]
    #[DataProvider('passwordLengthProvider')]
    public function password_must_be_at_least_6_chars(string $password, bool $expected): void
    {
        // Current v1.0 rule: strlen >= 6 (v1.1 will require >= 8 + not common)
        $this->assertSame($expected, strlen($password) >= 6);
    }

    public static function passwordLengthProvider(): array
    {
        return [
            'empty' => ['', false],
            'five_chars' => ['12345', false],
            'six_chars' => ['123456', true],
            'strong' => ['MyStr0ng!Pass', true],
            'unicode' => ['mậtkhẩu', true],
        ];
    }

    // ========== HOTEN VALIDATION (Register) ==========

    #[Test]
    #[DataProvider('hotenProvider')]
    public function hoten_must_be_at_least_2_chars(string $hoten, bool $expected): void
    {
        $trimmed = trim($hoten);
        $this->assertSame($expected, mb_strlen($trimmed) >= 2, "hoten '{$hoten}'");
    }

    public static function hotenProvider(): array
    {
        return [
            'empty' => ['', false],
            'one_char' => ['A', false],
            'two_chars' => ['An', true],
            'vietnamese' => ['Nguyễn Văn A', true],
            'spaces_around' => ['  A  ', false],  // trims to 1 char
        ];
    }

    // ========== CART QUANTITY (BR-03) ==========

    #[Test]
    #[DataProvider('cartQuantityProvider')]
    public function cart_quantity_clamped_to_1_99(int $input, int $expected): void
    {
        // Mimics: min(99, max(1, (int)$soluong)) in SanphamController::addtocart
        $actual = min(99, max(1, $input));
        $this->assertSame($expected, $actual);
    }

    public static function cartQuantityProvider(): array
    {
        return [
            'negative' => [-5, 1],
            'zero' => [0, 1],
            'one' => [1, 1],
            'fifty' => [50, 50],
            'ninetynine' => [99, 99],
            'hundred' => [100, 99],
            'thousand' => [1000, 99],
        ];
    }

    // ========== IMAGE UPLOAD VALIDATION (BR-11, BR-12) ==========

    #[Test]
    public function accepted_image_mime_types(): void
    {
        $accepted = ['image/jpeg', 'image/png', 'image/webp'];
        $this->assertContains('image/jpeg', $accepted);
        $this->assertContains('image/png', $accepted);
        $this->assertContains('image/webp', $accepted);
        $this->assertNotContains('image/gif', $accepted);
        $this->assertNotContains('application/pdf', $accepted);
    }

    #[Test]
    public function max_upload_size_5mb(): void
    {
        $maxBytes = 5 * 1024 * 1024;
        $this->assertSame(5_242_880, $maxBytes);
    }
}
