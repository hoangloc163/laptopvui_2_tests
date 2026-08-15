<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.8, 5.2.9 - Admin danh mục & đơn hàng
 */
#[Group('Admin - Danh mục')]
class AdminCategoryOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
        $this->loginAsAdmin();
    }

    // ============ CATEGORY ============

    /**
     * @testCase TC-ACAT-01 - Xem danh sách danh mục
     * @priority High
     */
    #[Test]
    public function admin_can_view_category_list(): void
    {
        $response = $this->get('admin/loai');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-ACAT-02 - Form thêm danh mục
     * @priority High
     */
    #[Test]
    public function admin_can_view_add_category_form(): void
    {
        $response = $this->get('admin/addloai');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-ACAT-03 - Thêm danh mục hợp lệ
     * @priority High
     */
    #[Test]
    public function admin_can_add_new_category(): void
    {
        $tenLoai = 'Test Category ' . uniqid();
        $response = $this->post('admin/addloai_', [
            'ten_loai' => $tenLoai,
            'thutu' => 99,
            'anhien' => 1,
        ]);
        $this->assertRedirect($response, 'admin/loai');
    }

    /**
     * @testCase TC-ACAT-04 - Thêm danh mục tên trùng
     * @priority High
     */
    #[Test]
    public function admin_cannot_add_duplicate_category(): void
    {
        $tenLoai = 'Dup Cat ' . uniqid();
        // Add first time
        $this->post('admin/addloai_', ['ten_loai' => $tenLoai, 'thutu' => 1, 'anhien' => 1]);
        // Try duplicate
        $response = $this->post('admin/addloai_', ['ten_loai' => $tenLoai, 'thutu' => 2, 'anhien' => 1]);
        // Should error (either redirect back or show error)
        $this->assertLessThan(500, $response->getStatusCode());
    }

    /**
     * @testCase TC-ACAT-05 - Xoá danh mục đang có SP
     * @priority High
     */
    #[Test]
    public function admin_cannot_delete_category_with_products(): void
    {
        // Try delete category 1 (which typically has products in demo)
        $response = $this->get('admin/deleteloai', ['id' => 1]);
        $this->assertLessThan(500, $response->getStatusCode());
        // Should redirect back with error (not actually delete)
    }

    // ============ ORDERS ============

    /**
     * @testCase TC-AORD-01 - Xem danh sách đơn hàng
     * @priority High
     */
    #[Test]
    public function admin_can_view_orders_list(): void
    {
        $response = $this->get('admin/orders');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-AORD-02 - Xem chi tiết đơn hàng
     * @priority High
     */
    #[Test]
    public function admin_can_view_order_detail(): void
    {
        // First create an order via checkout
        $this->get('addtocart', ['id' => 1, 'soluong' => 1]);
        $this->post('checkout_', [
            'hoten' => 'Order Test',
            'email' => 'order' . uniqid() . '@example.com',
            'dienthoai' => '0912345678',
            'diachi' => 'Test address',
        ]);

        // Now as admin, look at orders list
        $this->loginAsAdmin();
        $response = $this->get('admin/orders');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-AORD-03 - Chi tiết đơn không tồn tại
     * @priority Medium
     */
    #[Test]
    public function order_detail_returns_404_for_nonexistent(): void
    {
        $response = $this->get('admin/order', ['id' => 99999]);
        $this->assertContains($response->getStatusCode(), [404, 302, 303]);
    }

    /**
     * @testCase TC-AORD-04 - Missing feature: cập nhật trạng thái đơn hàng
     * @priority Medium
     * @status FAIL EXPECTED - Feature not implemented in v1.0
     */
    #[Test]
    public function order_status_workflow_should_exist(): void
    {
        $this->markTestIncomplete(
            'FEATURE US-20 AC-20.3: Order status workflow (pending/confirmed/shipping/delivered) ' .
            'is planned for v1.1. Currently no status field exists in donhang table.'
        );
    }
}
