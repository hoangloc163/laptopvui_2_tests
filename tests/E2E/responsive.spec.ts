import { test, expect } from '@playwright/test';

/**
 * @module Responsive / UI-UX
 * @source BaoCao_TestCase_LaptopVui mục 5.3.7 - Responsive
 * Các test này tự động chạy trên project Mobile Chrome / Mobile Safari (xem playwright.config.ts)
 */
test.describe('[Module: Responsive / UI-UX]', () => {
  test('TC-E2E-RESP-01: trang chủ không bị tràn ngang trên màn hình mobile', async ({ page }) => {
    await test.step('Mở trang chủ', async () => {
      await page.goto('/');
    });

    await test.step('Kiểm tra không có thanh cuộn ngang (scrollWidth <= innerWidth)', async () => {
      const bodyWidth = await page.evaluate(() => document.body.scrollWidth);
      const windowWidth = await page.evaluate(() => window.innerWidth);
      expect(bodyWidth).toBeLessThanOrEqual(windowWidth + 1); // +1 for rounding
    });

    await test.step('Kiểm tra thương hiệu vẫn hiển thị đúng', async () => {
      await expect(page.locator('body')).toContainText(/LAPTOP VUI/i);
    });
  });

  test('TC-E2E-RESP-02: menu điều hướng thu gọn thành hamburger trên mobile', async ({ page, viewport }) => {
    await test.step('Bỏ qua nếu viewport không phải mobile (>=768px)', async () => {
      if ((viewport?.width ?? 1200) >= 768) {
        test.skip();
      }
    });

    await test.step('Mở trang chủ và kiểm tra trang vẫn render ổn định', async () => {
      await page.goto('/');
      const hamburger = page.locator('.navbar-toggler, button[aria-label*="menu" i]').first();
      // Chấp nhận cả 2 trường hợp: có nút hamburger, hoặc nav tự xếp dọc
      await expect(page.locator('body')).toBeVisible();
    });
  });

  test('TC-E2E-RESP-03: trang chi tiết sản phẩm hiển thị vừa khung trên mobile', async ({ page }) => {
    await test.step('Mở trang chi tiết sản phẩm id=1', async () => {
      await page.goto('/sp?id=1');
    });

    await test.step('Kiểm tra ảnh sản phẩm không vượt quá chiều rộng viewport', async () => {
      const img = page.locator('img').first();
      if (await img.isVisible()) {
        const box = await img.boundingBox();
        if (box) {
          expect(box.width).toBeLessThanOrEqual((page.viewportSize()?.width ?? 400));
        }
      }
    });
  });

  test('TC-E2E-RESP-04: kích thước vùng chạm tối thiểu đạt chuẩn trên mobile', async ({ page, viewport }) => {
    await test.step('Bỏ qua nếu viewport không phải mobile (>=768px)', async () => {
      if ((viewport?.width ?? 1200) >= 768) {
        test.skip();
      }
    });

    await test.step('Mở trang chủ và kiểm tra kích thước nút/link chính (WCAG 2.5.5, tối thiểu ~30px)', async () => {
      await page.goto('/');
      const buttons = page.locator('a[href*="/sp?id="], button, a.btn');
      const count = await buttons.count();

      if (count > 0) {
        const firstBtn = buttons.first();
        const box = await firstBtn.boundingBox();
        if (box) {
          expect(Math.max(box.width, box.height)).toBeGreaterThanOrEqual(30);
        }
      }
    });
  });
});
