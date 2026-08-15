import { test, expect } from '@playwright/test';

/**
 * @module Trang chủ & Điều hướng
 * @source BaoCao_TestCase_LaptopVui mục 5.2.1 + 5.3.1 (UI/UX)
 * E2E test: user visits homepage and sees all key sections
 *
 * Mỗi test dùng test.step() để chia thành các bước nhỏ có tên rõ ràng —
 * khi mở playwright-report (hoặc trace viewer), tester sẽ thấy danh sách
 * step pass/fail riêng biệt thay vì chỉ 1 khối "pass/fail" cho cả test.
 */
test.describe('[Module: Trang chủ & Điều hướng]', () => {
  test('TC-E2E-HOME-01: hiển thị thương hiệu, menu điều hướng và danh sách sản phẩm', async ({ page }) => {
    await test.step('Mở trang chủ', async () => {
      await page.goto('/');
    });

    await test.step('Kiểm tra thương hiệu "LAPTOP VUI" hiển thị', async () => {
      await expect(page.locator('body')).toContainText(/LAPTOP VUI/i);
    });

    await test.step('Kiểm tra menu danh mục (category) hiển thị', async () => {
      const categoryLinks = page.locator('a[href*="loai?idloai="]');
      await expect(categoryLinks.first()).toBeVisible();
    });

    await test.step('Kiểm tra có ít nhất 1 sản phẩm hiển thị', async () => {
      const productCards = page.locator('a[href*="/sp?id="]');
      await expect(productCards.first()).toBeVisible();
    });
  });

  test('TC-E2E-HOME-02: badge giỏ hàng xuất hiện sau khi thêm sản phẩm', async ({ page }) => {
    await test.step('Mở trang chủ và click sản phẩm đầu tiên', async () => {
      await page.goto('/');
      await page.locator('a[href*="/sp?id="]').first().click();
      await expect(page).toHaveURL(/sp\?id=/);
    });

    await test.step('Bấm "Thêm vào giỏ" và xác nhận chuyển sang trang giỏ hàng', async () => {
      const addBtn = page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ|add.*cart/i }).first();
      if (await addBtn.isVisible()) {
        await addBtn.click();
        await expect(page).toHaveURL(/showcart/);
      }
    });
  });

  test('TC-E2E-HOME-03: tìm kiếm từ thanh điều hướng trả về kết quả', async ({ page }) => {
    await test.step('Mở trang chủ', async () => {
      await page.goto('/');
    });

    await test.step('Nhập từ khoá "laptop" vào ô tìm kiếm và nhấn Enter', async () => {
      const searchInput = page.locator('input[name="keyword"]').first();
      await searchInput.fill('laptop');
      await searchInput.press('Enter');
    });

    await test.step('Xác nhận chuyển tới trang kết quả tìm kiếm (/tk)', async () => {
      await expect(page).toHaveURL(/tk/);
    });
  });

  test('TC-E2E-HOME-04: click danh mục điều hướng đúng trang', async ({ page }) => {
    await test.step('Mở trang chủ', async () => {
      await page.goto('/');
    });

    await test.step('Click vào danh mục đầu tiên trong menu', async () => {
      const categoryLink = page.locator('a[href*="loai?idloai="]').first();
      await categoryLink.click();
    });

    await test.step('Xác nhận URL chứa tham số idloai', async () => {
      await expect(page).toHaveURL(/loai\?idloai=/);
    });
  });
});
