<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.3 - Tìm kiếm
 */
class SearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    /**
     * @testCase TC-SEARCH-01 - GET /tk hiển thị form
     * @priority Medium
     */
    #[Test]
    public function it_shows_search_form_on_get(): void
    {
        $response = $this->get('tk');
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-SEARCH-02 - Search với keyword hợp lệ
     * @priority High
     */
    #[Test]
    public function it_returns_results_for_matching_keyword(): void
    {
        $response = $this->post('tk', ['keyword' => 'Dell']);
        $this->assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        // Should contain "Dell" in result page
        $this->assertStringContainsStringIgnoringCase('Dell', $body);
    }

    /**
     * @testCase TC-SEARCH-03 - Search keyword rỗng
     * @priority Medium
     */
    #[Test]
    public function it_handles_empty_keyword(): void
    {
        $response = $this->post('tk', ['keyword' => '']);
        // Should not 500, either show all or empty state
        $this->assertLessThan(500, $response->getStatusCode());
    }

    /**
     * @testCase TC-SEARCH-04 - Search với keyword không match
     * @priority High
     */
    #[Test]
    public function it_shows_empty_state_when_no_results(): void
    {
        $response = $this->post('tk', ['keyword' => 'ZZZ_NOT_EXIST_KEYWORD_XYZ']);
        $this->assertSame(200, $response->getStatusCode());
        // Body should not error, either shows 0 results or "không tìm thấy"
        $body = (string)$response->getBody();
        $this->assertNotEmpty($body);
    }

    /**
     * @testCase TC-SEARCH-05 - Search với ký tự đặc biệt (SQL injection attempt)
     * @priority High
     */
    #[Test]
    public function it_safely_handles_sql_injection_attempt(): void
    {
        $response = $this->post('tk', ['keyword' => "'; DROP TABLE sanpham;--"]);
        // Should not 500 (prepared statement protects)
        $this->assertLessThan(500, $response->getStatusCode());

        // Verify products still exist after "attack"
        $home = $this->get('');
        $this->assertSame(200, $home->getStatusCode(), 'Products table should still exist');
    }

    /**
     * @testCase TC-SEARCH-06 - Search với XSS payload
     * @priority High
     */
    #[Test]
    public function it_escapes_xss_in_search_output(): void
    {
        $payload = '<script>alert(1)</script>';
        $response = $this->post('tk', ['keyword' => $payload]);
        $body = (string)$response->getBody();
        // Should not echo the raw script tag
        $this->assertStringNotContainsString('<script>alert(1)</script>', $body,
            'XSS payload should be escaped in output');
    }
}
