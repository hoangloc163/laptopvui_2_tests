<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.7 - Admin Auth
 */
#[Group('Admin - Đăng nhập')]
class AdminAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    /**
     * @testCase TC-ADMIN-01 - GET /admin khi chưa đăng nhập → redirect login
     * @priority High
     */
    #[Test]
    public function unauthenticated_admin_access_redirects_to_login(): void
    {
        $response = $this->get('admin');
        $this->assertRedirect($response, 'admin/login');
    }

    /**
     * @testCase TC-ADMIN-02 - GET /admin/sp khi chưa đăng nhập
     * @priority High
     */
    #[Test]
    public function unauthenticated_cannot_access_admin_products(): void
    {
        $response = $this->get('admin/sp');
        $this->assertRedirect($response, 'admin/login');
    }

    /**
     * @testCase TC-ADMIN-03 - Login admin với credentials demo
     * @priority High
     */
    #[Test]
    public function it_logs_in_admin_with_valid_credentials(): void
    {
        $response = $this->post('admin/login_', [
            'email' => 'admin@demo.local',
            'matkhau' => 'admin123',
        ]);
        $this->assertRedirect($response, 'admin');
    }

    /**
     * @testCase TC-ADMIN-04 - Login admin sai mật khẩu
     * @priority High
     */
    #[Test]
    public function it_rejects_wrong_admin_password(): void
    {
        $response = $this->post('admin/login_', [
            'email' => 'admin@demo.local',
            'matkhau' => 'wrongpassword',
        ]);
        $this->assertRedirect($response, 'admin/login');
    }

    /**
     * @testCase TC-ADMIN-05 - Login admin với user vaitro=0
     * @priority High
     */
    #[Test]
    public function it_rejects_regular_user_from_admin_login(): void
    {
        // Register regular user
        $email = 'reguser_' . uniqid() . '@example.com';
        $this->post('register_', [
            'hoten' => 'Regular User',
            'email' => $email,
            'matkhau' => 'password123',
        ]);

        // Try to login as admin
        $response = $this->post('admin/login_', [
            'email' => $email,
            'matkhau' => 'password123',
        ]);
        $this->assertRedirect($response, 'admin/login');
    }

    /**
     * @testCase TC-ADMIN-06 - After admin login, /admin dashboard loads
     * @priority High
     */
    #[Test]
    public function admin_can_access_dashboard_after_login(): void
    {
        $this->loginAsAdmin();
        $response = $this->get('admin');
        $this->assertSame(200, $response->getStatusCode());

        $body = strtolower((string)$response->getBody());
        // Dashboard should have stats
        $this->assertTrue(
            str_contains($body, 'sản phẩm') || str_contains($body, 'thống kê') || str_contains($body, 'đơn hàng'),
            'Dashboard should show admin stats/labels'
        );
    }

    /**
     * @testCase TC-ADMIN-07 - Admin logout
     * @priority Medium
     */
    #[Test]
    public function admin_can_logout(): void
    {
        $this->loginAsAdmin();
        $response = $this->get('admin/logout');
        $this->assertRedirect($response);

        // After logout, /admin should redirect to login again
        $adminResp = $this->get('admin');
        $this->assertRedirect($adminResp, 'admin/login');
    }

    /**
     * @testCase TC-ADMIN-08 - CSRF protection missing on admin destructive GET
     * @priority High
     * @status FAIL EXPECTED - No CSRF token yet
     */
    #[Test]
    public function admin_delete_product_should_require_csrf(): void
    {
        $this->markTestIncomplete(
            'BUG US-26/US-18 AC-18.2: /admin/deletesp?id= uses GET without CSRF token. ' .
            'Vulnerable to CSRF attack. FIX planned in M0 (convert to POST + CSRF).'
        );
    }
}
