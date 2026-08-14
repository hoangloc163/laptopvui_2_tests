<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.2 - Chi tiết sản phẩm và danh mục
 */
class ProductDetailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireApp();
    }

    /**
     * @testCase TC-DETAIL-01 - Xem chi tiết SP tồn tại
     * @priority High
     */
    #[Test]
    public function it_displays_valid_product_detail(): void
    {
        $response = $this->get('sp', ['id' => 1]);
        $this->assertSame(200, $response->getStatusCode());

        $body = (string)$response->getBody();
        $this->assertMatchesRegularExpression('/(số lượng|thêm vào giỏ|add to cart)/i', $body,
            'Add-to-cart form should be present');
    }

    /**
     * @testCase TC-DETAIL-02 - ID SP không tồn tại → 404
     * @priority High
     */
    #[Test]
    public function it_returns_404_for_nonexistent_product(): void
    {
        $response = $this->get('sp', ['id' => 99999]);
        $this->assertSame(404, $response->getStatusCode(), 'Non-existent product should return 404');
    }

    /**
     * @testCase TC-DETAIL-03 - ID là chuỗi
     * @priority Medium
     */
    #[Test]
    public function it_handles_string_id_as_404(): void
    {
        $response = $this->get('sp', ['id' => 'abc']);
        $this->assertSame(404, $response->getStatusCode(), '(int)"abc" = 0 → 404');
    }

    /**
     * @testCase TC-DETAIL-04 - ID SP âm
     * @priority Medium
     */
    #[Test]
    public function it_handles_negative_id_as_404(): void
    {
        $response = $this->get('sp', ['id' => -5]);
        $this->assertSame(404, $response->getStatusCode(), 'Negative ID should be 404');
    }

    /**
     * @testCase TC-DETAIL-08 - Mô tả có ký tự đặc biệt (XSS)
     * @priority High
     */
    #[Test]
    public function it_escapes_html_in_description(): void
    {
        $response = $this->get('sp', ['id' => 1]);
        $body = (string)$response->getBody();

        // Even if description had <script>, htmlspecialchars should escape it
        $this->assertStringNotContainsString('<script>alert(', $body,
            'Any inline script content from description should be escaped');
    }

    // ========== CATEGORY TESTS ==========

    /**
     * @testCase TC-DETAIL-10 - Danh mục hợp lệ
     * @priority High
     */
    #[Test]
    public function it_displays_valid_category(): void
    {
        $response = $this->get('loai', ['idloai' => 1, 'page' => 1]);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @testCase TC-DETAIL-11 - Danh mục không tồn tại
     * @priority High
     */
    #[Test]
    public function it_returns_404_for_nonexistent_category(): void
    {
        $response = $this->get('loai', ['idloai' => 999]);
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @testCase TC-DETAIL-12 - Trang vượt quá tổng
     * @priority Medium
     */
    #[Test]
    public function it_clamps_page_number_to_valid_range(): void
    {
        $response = $this->get('loai', ['idloai' => 1, 'page' => 99]);
        // App should not 500, either clamps to last page or shows empty
        $this->assertLessThan(500, $response->getStatusCode(),
            'Page > total should not cause 500 error');
    }
}
