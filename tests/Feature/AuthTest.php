<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.6 - Đăng ký / Đăng nhập
 */
#[Group('Đăng ký / Đăng nhập')]
class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    // ============ REGISTER ============

    /**
     * @testCase TC-AUTH-01 - GET /register hiển thị form
     * @priority High
     */
    #[Test]
    public function it_displays_registration_form(): void
    {
        $response = $this->get('register');
        $this->assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        $this->assertStringContainsStringIgnoringCase('đăng ký', $body);
    }

    /**
     * @testCase TC-AUTH-02 - Đăng ký hợp lệ
     * @priority High
     */
    #[Test]
    public function it_registers_new_user_with_valid_data(): void
    {
        $uniqueEmail = 'user_' . uniqid() . '@example.com';
        $response = $this->post('register_', [
            'hoten' => 'Test User',
            'email' => $uniqueEmail,
            'matkhau' => 'password123',
        ]);
        $this->assertRedirect($response, 'login');
    }

    /**
     * @testCase TC-AUTH-03 - Họ tên < 2 ký tự
     * @priority High
     */
    #[Test]
    public function it_rejects_short_name(): void
    {
        $response = $this->post('register_', [
            'hoten' => 'A',
            'email' => 'test' . uniqid() . '@example.com',
            'matkhau' => 'password123',
        ]);
        $this->assertRedirect($response, 'register');
    }

    /**
     * @testCase TC-AUTH-04 - Email không hợp lệ
     * @priority High
     */
    #[Test]
    public function it_rejects_invalid_email_on_register(): void
    {
        $response = $this->post('register_', [
            'hoten' => 'Test User',
            'email' => 'invalid-email',
            'matkhau' => 'password123',
        ]);
        $this->assertRedirect($response, 'register');
    }

    /**
     * @testCase TC-AUTH-05 - Mật khẩu < 6 ký tự
     * @priority High
     */
    #[Test]
    public function it_rejects_short_password(): void
    {
        $response = $this->post('register_', [
            'hoten' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'matkhau' => '12345',
        ]);
        $this->assertRedirect($response, 'register');
    }

    /**
     * @testCase TC-AUTH-06 - Email đã tồn tại
     * @priority High
     */
    #[Test]
    public function it_rejects_duplicate_email(): void
    {
        $email = 'dup_' . uniqid() . '@example.com';
        // Register first time
        $this->post('register_', [
            'hoten' => 'First',
            'email' => $email,
            'matkhau' => 'password123',
        ]);
        // Try again with same email
        $response = $this->post('register_', [
            'hoten' => 'Second',
            'email' => $email,
            'matkhau' => 'anotherpass',
        ]);
        $this->assertRedirect($response, 'register');
    }

    /**
     * @testCase TC-AUTH-07 - Email được lowercase + trim
     * @priority Medium
     */
    #[Test]
    public function it_normalizes_email_on_register(): void
    {
        $unique = uniqid();
        // Register with UPPERCASE + spaces
        $this->post('register_', [
            'hoten' => 'Test',
            'email' => '  TEST' . $unique . '@EXAMPLE.COM  ',
            'matkhau' => 'password123',
        ]);
        // Try again with lowercase - should be rejected as duplicate
        $response = $this->post('register_', [
            'hoten' => 'Test2',
            'email' => 'test' . $unique . '@example.com',
            'matkhau' => 'password456',
        ]);
        $this->assertRedirect($response, 'register');
    }

    // ============ LOGIN ============

    /**
     * @testCase TC-AUTH-10 - Login đúng credentials
     * @priority High
     */
    #[Test]
    public function it_logs_in_with_valid_credentials(): void
    {
        // Register first
        $email = 'login_' . uniqid() . '@example.com';
        $this->post('register_', [
            'hoten' => 'Login Test',
            'email' => $email,
            'matkhau' => 'password123',
        ]);

        // Now login
        $response = $this->post('login_', [
            'email' => $email,
            'matkhau' => 'password123',
        ]);
        $this->assertRedirect($response);
        // Should go to home, not back to login
        $location = $response->getHeaderLine('Location');
        $this->assertStringNotContainsString('/login', $location);
    }

    /**
     * @testCase TC-AUTH-11 - Login email không tồn tại
     * @priority High
     */
    #[Test]
    public function it_rejects_nonexistent_email(): void
    {
        $response = $this->post('login_', [
            'email' => 'nonexistent_' . uniqid() . '@example.com',
            'matkhau' => 'anypassword',
        ]);
        $this->assertRedirect($response, 'login');
    }

    /**
     * @testCase TC-AUTH-12 - Login sai mật khẩu
     * @priority High
     */
    #[Test]
    public function it_rejects_wrong_password(): void
    {
        // Register first
        $email = 'wrongpass_' . uniqid() . '@example.com';
        $this->post('register_', [
            'hoten' => 'Test',
            'email' => $email,
            'matkhau' => 'correctpassword',
        ]);

        // Login with wrong password
        $response = $this->post('login_', [
            'email' => $email,
            'matkhau' => 'wrongpassword',
        ]);
        $this->assertRedirect($response, 'login');
    }

    /**
     * @testCase TC-AUTH-13 - User enumeration test
     * @priority High
     * @status FAIL EXPECTED - Currently reveals which is wrong (BUG)
     */
    #[Test]
    public function login_should_not_reveal_which_credential_is_wrong(): void
    {
        $this->markTestIncomplete(
            'BUG US-11 AC-11.3: Login currently returns different messages for ' .
            '"Email không tồn tại" vs "Mật khẩu không đúng" - allows user enumeration. ' .
            'FIX planned in M0.'
        );
    }

    /**
     * @testCase TC-AUTH-14 - Rate limit login
     * @priority High
     * @status FAIL EXPECTED - No rate limit yet
     */
    #[Test]
    public function login_should_have_rate_limit(): void
    {
        $this->markTestIncomplete(
            'BUG US-11 AC-11.5: No rate limit on login endpoint - vulnerable to brute-force. ' .
            'FIX planned in M0.'
        );
    }

    // ============ LOGOUT ============

    /**
     * @testCase TC-AUTH-15 - Logout xoá session
     * @priority High
     */
    #[Test]
    public function it_logs_out_successfully(): void
    {
        // Register and login
        $email = 'logout_' . uniqid() . '@example.com';
        $this->post('register_', [
            'hoten' => 'Logout Test',
            'email' => $email,
            'matkhau' => 'password123',
        ]);
        $this->post('login_', ['email' => $email, 'matkhau' => 'password123']);

        // Logout
        $response = $this->get('logout');
        $this->assertRedirect($response);
    }
}
