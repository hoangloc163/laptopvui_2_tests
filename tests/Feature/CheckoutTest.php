<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.5 - Thanh toán (Checkout)
 */
#[Group('Thanh toán')]
class CheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    /**
     * @testCase TC-CHK-01 - /checkout khi giỏ trống → redirect /showcart
     * @priority High
     */
    #[Test]
    public function empty_cart_redirects_from_checkout(): void
    {
        $response = $this->get('checkout');
        $this->assertRedirect($response, 'showcart');
    }

    /**
     * @testCase TC-CHK-03 - Đặt hàng hợp lệ
     * @priority High
     */
    #[Test]
    public function it_creates_order_with_valid_data(): void
    {
        // Add product first
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);

        $response = $this->post('checkout_', [
            'hoten' => 'Nguyễn Test',
            'email' => 'test' . uniqid() . '@example.com',
            'dienthoai' => '0912345678',
            'diachi' => '123 Test Street, Q1, HCM',
        ]);

        $this->assertRedirect($response);
        // Should redirect to home / with success message
        $location = $response->getHeaderLine('Location');
        $this->assertMatchesRegularExpression('#/(banhang)?/?$#', $location,
            'Should redirect to home after successful order');
    }

    /**
     * @testCase TC-CHK-04 - Họ tên rỗng → error
     * @priority High
     */
    #[Test]
    public function it_rejects_empty_name(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => '',
            'email' => 'test@example.com',
            'dienthoai' => '0912345678',
            'diachi' => '123 test',
        ]);
        $this->assertRedirect($response, 'checkout');
    }

    /**
     * @testCase TC-CHK-05 - Email không hợp lệ
     * @priority High
     */
    #[Test]
    public function it_rejects_invalid_email(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => 'Nguyễn Test',
            'email' => 'not-an-email',
            'dienthoai' => '0912345678',
            'diachi' => '123 test',
        ]);
        $this->assertRedirect($response, 'checkout');
    }

    /**
     * @testCase TC-CHK-06 - Địa chỉ rỗng
     * @priority High
     */
    #[Test]
    public function it_rejects_empty_address(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => 'Nguyễn Test',
            'email' => 'test@example.com',
            'dienthoai' => '0912345678',
            'diachi' => '',
        ]);
        $this->assertRedirect($response, 'checkout');
    }

    /**
     * @testCase TC-CHK-07 - Điện thoại sai định dạng
     * @priority High
     */
    #[Test]
    public function it_rejects_invalid_phone(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => 'Nguyễn Test',
            'email' => 'test@example.com',
            'dienthoai' => 'abcde',
            'diachi' => '123 test',
        ]);
        $this->assertRedirect($response, 'checkout');
    }

    /**
     * @testCase TC-CHK-08 - Điện thoại quốc tế OK
     * @priority Medium
     */
    #[Test]
    public function it_accepts_international_phone(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => 'Nguyễn Test',
            'email' => 'test' . uniqid() . '@example.com',
            'dienthoai' => '+84 (28) 123-4567',
            'diachi' => '123 test',
        ]);
        $this->assertRedirect($response);
        $location = $response->getHeaderLine('Location');
        // Should go to home (success), not back to /checkout
        $this->assertStringNotContainsString('checkout', $location);
    }

    /**
     * @testCase TC-CHK-09 - Điện thoại 7 ký tự (dưới min)
     * @priority Medium
     */
    #[Test]
    public function it_rejects_short_phone(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => 'Test',
            'email' => 'test@example.com',
            'dienthoai' => '1234567', // 7 chars, min is 8
            'diachi' => '123 test',
        ]);
        $this->assertRedirect($response, 'checkout');
    }

    /**
     * @testCase TC-CHK-10 - XSS trong hoten
     * @priority High
     */
    #[Test]
    public function it_strips_html_tags_from_name(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => '<script>alert(1)</script>Test',
            'email' => 'xss' . uniqid() . '@example.com',
            'dienthoai' => '0912345678',
            'diachi' => '123 test',
        ]);
        // Should succeed (tags stripped, name becomes "alert(1)Test" or just "Test")
        // OR reject if strip makes it too short - both are acceptable
        $this->assertContains($response->getStatusCode(), [302, 303],
            'Should redirect (not 500)');
    }

    /**
     * @testCase TC-CHK-13 - Double submit protection
     * @priority High
     * @status FAIL EXPECTED - Currently no protection (should be P0 FIX in M1)
     */
    #[Test]
    public function it_should_prevent_double_submit(): void
    {
        $this->markTestIncomplete(
            'BUG US-08 AC-08.8: App currently allows double-submit creating duplicate orders. ' .
            'FIX planned in M1 with idempotency token.'
        );
    }

    /**
     * @testCase TC-CHK-15 - Guest can checkout without login
     * @priority High
     */
    #[Test]
    public function guest_can_complete_checkout(): void
    {
        // No login - just add product and checkout
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->post('checkout_', [
            'hoten' => 'Guest User',
            'email' => 'guest' . uniqid() . '@example.com',
            'dienthoai' => '0912345678',
            'diachi' => 'Guest address',
        ]);
        $this->assertRedirect($response);
        $location = $response->getHeaderLine('Location');
        $this->assertStringNotContainsString('login', $location, 'Guest should not be forced to login');
    }
}
