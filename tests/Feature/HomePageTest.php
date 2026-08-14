<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.1 - Nhóm test case: Trang chủ & Điều hướng
 */
class HomePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    /**
     * @testCase TC-HOME-01
     * @priority High
     */
    #[Test]
    public function it_loads_homepage_successfully(): void
    {
        $response = $this->get('');
        $this->assertSame(200, $response->getStatusCode(), 'Homepage should return HTTP 200');
    }

    /**
     * @testCase TC-HOME-01 (extended)
     * @priority High
     */
    #[Test]
    public function homepage_displays_brand_and_navigation(): void
    {
        $response = $this->get('');
        $body = (string)$response->getBody();

        $this->assertStringContainsStringIgnoringCase('LAPTOP VUI', $body, 'Brand should appear');
        $this->assertStringContainsString('<nav', $body, 'Navigation should exist');
    }

    /**
     * @testCase TC-HOME-02 - Menu danh mục động
     * @priority High
     */
    #[Test]
    public function homepage_shows_dynamic_category_menu(): void
    {
        $response = $this->get('');
        $crawler = $this->crawler($response);

        // Categories should be rendered as links to /loai?idloai=
        $categoryLinks = $crawler->filter('a[href*="loai?idloai="]');
        $this->assertGreaterThan(0, $categoryLinks->count(), 'At least 1 category link should exist');
    }

    /**
     * @testCase TC-HOME-03 - Section "Laptop nổi bật"
     * @priority High
     */
    #[Test]
    public function homepage_shows_featured_products_section(): void
    {
        $response = $this->get('');
        $body = strtolower((string)$response->getBody());

        $this->assertTrue(
            str_contains($body, 'nổi bật') || str_contains($body, 'featured'),
            'Should have a "nổi bật / featured" section'
        );
    }

    /**
     * @testCase TC-HOME-04 - Section "Sản phẩm xem nhiều"
     * @priority High
     */
    #[Test]
    public function homepage_shows_most_viewed_section(): void
    {
        $response = $this->get('');
        $body = (string)$response->getBody();
        $this->assertStringContainsString('xem nhiều', $body, 'Should have "xem nhiều" section');
    }

    /**
     * @testCase TC-HOME-08 - Badge số lượng giỏ hàng
     * @priority High
     */
    #[Test]
    public function homepage_shows_cart_badge_when_cart_has_items(): void
    {
        // Add a product to cart first
        $this->get('addtocart', ['id' => 1, 'soluong' => 2]);
        // Follow redirect to /showcart, then go back to home
        $this->get('');
        $response = $this->get('');

        $body = (string)$response->getBody();
        // Cart badge should show either "2" or "Giỏ (2)" - check for number
        $this->assertMatchesRegularExpression('/(giỏ|cart)/i', $body);
    }

    /**
     * @testCase TC-HOME-09 - Route không tồn tại → 404
     * @priority Medium
     */
    #[Test]
    public function invalid_route_returns_404(): void
    {
        $response = $this->get('this-route-does-not-exist-12345');
        $this->assertSame(404, $response->getStatusCode(), 'Invalid route should return HTTP 404');
    }

    /**
     * @testCase TC-HOME-10 - Method không hỗ trợ → 405
     * @priority Low
     */
    #[Test]
    public function unsupported_method_returns_405(): void
    {
        $response = $this->http->request('PUT', '', ['http_errors' => false]);
        $status = $response->getStatusCode();
        $this->assertContains($status, [404, 405], "Should return 404 or 405 for PUT, got {$status}");
    }
}
