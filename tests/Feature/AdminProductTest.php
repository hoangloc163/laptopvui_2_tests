<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.7 - Admin quản lý SP
 */
#[Group('Admin - Sản phẩm')]
class AdminProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
        $this->loginAsAdmin();
    }

    /**
     * @testCase TC-APROD-01 - Admin xem danh sách SP
     * @priority High
     */
    #[Test]
    public function admin_can_view_product_list(): void
    {
        $response = $this->get('admin/sp');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-APROD-02 - Danh sách paging
     * @priority Medium
     */
    #[Test]
    public function product_list_supports_pagination(): void
    {
        $response = $this->get('admin/sp', ['page' => 1]);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-APROD-03 - Tìm kiếm SP admin
     * @priority High
     */
    #[Test]
    public function admin_can_search_products(): void
    {
        $response = $this->get('admin/sp', ['keyword' => 'Dell']);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-APROD-04 - Sort by price DESC
     * @priority Medium
     */
    #[Test]
    public function admin_can_sort_products_by_price(): void
    {
        $response = $this->get('admin/sp', ['sort' => 'gia', 'order' => 'DESC']);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-APROD-05 - GET /admin/addsp form
     * @priority High
     */
    #[Test]
    public function admin_can_view_add_product_form(): void
    {
        $response = $this->get('admin/addsp');
        $this->assertSame(200, $response->getStatusCode());
        $body = strtolower((string)$response->getBody());
        $this->assertTrue(
            str_contains($body, 'thêm sản phẩm') || str_contains($body, 'add product'),
            'Add product form should be displayed'
        );
    }

    /**
     * @testCase TC-APROD-06 - GET /admin/editsp?id= với id hợp lệ
     * @priority High
     */
    #[Test]
    public function admin_can_view_edit_product_form(): void
    {
        $response = $this->get('admin/editsp', ['id' => 1]);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-APROD-07 - GET /admin/editsp?id=999 không tồn tại
     * @priority Medium
     */
    #[Test]
    public function edit_form_returns_404_for_nonexistent_product(): void
    {
        $response = $this->get('admin/editsp', ['id' => 99999]);
        $this->assertContains($response->getStatusCode(), [404, 302, 303]);
    }

    /**
     * @testCase TC-APROD-08 - Delete SP GET (BUG - should be POST)
     * @priority High
     * @status FAIL EXPECTED - Uses GET, vulnerable to CSRF
     */
    #[Test]
    public function delete_product_should_use_post_not_get(): void
    {
        $this->markTestIncomplete(
            'BUG AC-18.2: /admin/deletesp uses GET request. Vulnerable to CSRF. ' .
            'FIX planned in M0: convert to POST + CSRF token verification.'
        );
    }

    /**
     * @testCase TC-APROD-09 - Delete SP id không tồn tại
     * @priority Medium
     */
    #[Test]
    public function delete_nonexistent_product_does_not_crash(): void
    {
        $response = $this->get('admin/deletesp', ['id' => 99999]);
        // Should redirect (not 500), even if nothing was deleted
        $this->assertLessThan(500, $response->getStatusCode());
    }

    /**
     * @testCase TC-APROD-10 - Add SP với data hợp lệ (requires multipart)
     * @priority High
     * @status PARTIAL - Multipart file upload test requires real image file
     */
    #[Test]
    public function add_product_validation_missing_category(): void
    {
        // POST without id_loai - should validate fail
        // Note: real add requires multipart file upload; here we test validation path
        $response = $this->http->post('admin/addsp_', [
            'form_params' => [
                'ten_sp' => 'Test Product ' . uniqid(),
                'gia' => 1000000,
                'gia_km' => 0,
                'ngay' => date('Y-m-d'),
                'hot' => 0,
                'anhien' => 1,
                'mota' => 'Test description',
                // Missing id_loai
            ],
        ]);
        // Currently uses die() - would render as text/html 200 with error text
        // TODO after M1 fix: expect proper redirect + error_message
        $body = (string)$response->getBody();
        $this->assertTrue(
            $response->getStatusCode() >= 400 ||
            str_contains(strtolower($body), 'lỗi') ||
            str_contains(strtolower($body), 'chọn'),
            'Should show validation error for missing category'
        );
    }

    /**
     * @testCase TC-APROD-11 - Preserve form data on validation fail
     * @priority High
     * @status FAIL EXPECTED - die() loses form data
     */
    #[Test]
    public function form_should_preserve_data_on_validation_fail(): void
    {
        $this->markTestIncomplete(
            'BUG US-16 AC-16.6: die() on validation fail loses form data. ' .
            'FIX planned in M1: redirect back with $_SESSION[\'form_data\'].'
        );
    }

    /**
     * @testCase TC-APROD-12 - Delete SP đang có trong đơn hàng
     * @priority Medium
     * @status FAIL EXPECTED - Creates orphan data
     */
    #[Test]
    public function delete_product_with_orders_should_soft_delete(): void
    {
        $this->markTestIncomplete(
            'BUG AC-18.3: Hard delete of product referenced in orders creates orphan data ' .
            'in donhangchitiet. FIX planned in M1: soft delete via deleted_at column.'
        );
    }
}
