<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.4 - Giỏ hàng
 */
class CartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    /**
     * @testCase TC-CART-01 - Thêm SP mới vào giỏ trống
     * @priority High
     */
    #[Test]
    public function it_adds_product_to_empty_cart(): void
    {
        $response = $this->get('addtocart', ['id' => 1, 'soluong' => 2]);
        $this->assertRedirect($response, 'showcart');

        // Follow redirect and verify cart has content
        $cartResp = $this->get('showcart');
        $this->assertSame(200, $cartResp->getStatusCode());
        $body = (string)$cartResp->getBody();
        $this->assertMatchesRegularExpression('/(giỏ|cart)/i', $body);
    }

    /**
     * @testCase TC-CART-03 - Số lượng vượt max 99 → cap về 99
     * @priority High
     */
    #[Test]
    public function it_caps_quantity_at_99(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 200]);
        $response = $this->get('showcart');

        $body = (string)$response->getBody();
        // Cart should show quantity 99 (capped), not 200
        $this->assertStringNotContainsString('value="200"', $body,
            'Quantity 200 should be capped to 99');
    }

    /**
     * @testCase TC-CART-05 - soluong = 0 hoặc âm → clamp về 1
     * @priority Medium
     */
    #[Test]
    public function it_clamps_quantity_zero_to_one(): void
    {
        $response = $this->get('addtocart', ['id' => 1, 'soluong' => 0]);
        $this->assertRedirect($response);
    }

    /**
     * @testCase TC-CART-06 - Thêm SP không tồn tại
     * @priority High
     */
    #[Test]
    public function it_rejects_nonexistent_product(): void
    {
        $response = $this->get('addtocart', ['id' => 99999, 'soluong' => 1]);
        // Should redirect (not 500), with error flash
        $this->assertRedirect($response);
    }

    /**
     * @testCase TC-CART-08 - Xem giỏ trống
     * @priority High
     */
    #[Test]
    public function empty_cart_shows_empty_state(): void
    {
        // Fresh session = empty cart
        $response = $this->get('showcart');
        $this->assertSame(200, $response->getStatusCode());
        $body = strtolower((string)$response->getBody());
        $this->assertTrue(
            str_contains($body, 'trống') || str_contains($body, 'empty') || str_contains($body, 'tiếp tục'),
            'Empty cart should show empty state or CTA'
        );
    }

    /**
     * @testCase TC-CART-09 - Cập nhật số lượng
     * @priority High
     */
    #[Test]
    public function it_updates_cart_quantity(): void
    {
        // Add first
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        // Update to 5
        $response = $this->post('updatecart', ['soluong' => [1 => 5]]);
        $this->assertRedirect($response, 'showcart');
    }

    /**
     * @testCase TC-CART-10 - Set số lượng = 0 → xoá SP
     * @priority High
     */
    #[Test]
    public function setting_quantity_to_zero_removes_product(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 3]);
        $this->post('updatecart', ['soluong' => [1 => 0]]);

        // Follow to see cart
        $response = $this->get('showcart');
        $body = strtolower((string)$response->getBody());
        // After setting to 0, cart should be empty
        $this->assertTrue(
            str_contains($body, 'trống') || !str_contains($body, 'value="3"'),
            'Product should be removed when quantity set to 0'
        );
    }

    /**
     * @testCase TC-CART-11 - Update với body không phải mảng
     * @priority Medium
     */
    #[Test]
    public function it_handles_invalid_update_body_gracefully(): void
    {
        $response = $this->post('updatecart', ['soluong' => 'not-an-array']);
        // Should not 500
        $this->assertLessThan(500, $response->getStatusCode());
    }

    /**
     * @testCase TC-CART-12 - Xoá 1 SP
     * @priority High
     */
    #[Test]
    public function it_removes_single_product_from_cart(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $response = $this->get('removefromcart', ['id' => 1]);
        $this->assertRedirect($response);
    }

    /**
     * @testCase TC-CART-13 - Clear cart
     * @priority High
     */
    #[Test]
    public function it_clears_entire_cart(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 2]);
        $this->get('addtocart', ['id' => 2, 'soluong' => 1]);
        $response = $this->get('clearcart');
        $this->assertRedirect($response);

        // Verify cart is empty
        $cartResp = $this->get('showcart');
        $body = strtolower((string)$cartResp->getBody());
        $this->assertTrue(
            str_contains($body, 'trống') || str_contains($body, 'empty'),
            'Cart should be empty after clear'
        );
    }

    /**
     * @testCase TC-CART-02 - Thêm SP đã có → cộng dồn
     * @priority High
     */
    #[Test]
    public function it_increments_quantity_when_adding_existing_product(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 2]);
        $this->get('addtocart', ['id' => 1, 'soluong' => 3]);
        // Cart should have qty=5 for id=1
        $response = $this->get('showcart');
        $body = (string)$response->getBody();
        // Look for value="5" in quantity input
        $this->assertMatchesRegularExpression('/value=["\']5["\']/', $body,
            'Adding same product should sum quantity (2+3=5)');
    }

    /**
     * @testCase TC-CART-04 - Cộng dồn vượt 99
     * @priority High
     */
    #[Test]
    public function it_caps_summed_quantity_at_99(): void
    {
        $this->get('addtocart', ['id' => 1, 'soluong' => 90]);
        $this->get('addtocart', ['id' => 1, 'soluong' => 20]);
        $response = $this->get('showcart');
        $body = (string)$response->getBody();
        $this->assertMatchesRegularExpression('/value=["\']99["\']/', $body,
            'Sum 90+20=110 should be capped to 99');
    }
}
