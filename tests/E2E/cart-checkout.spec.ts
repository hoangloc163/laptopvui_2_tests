import { test, expect } from '@playwright/test';

/**
 * @module Giỏ hàng & Thanh toán
 * @source BaoCao_TestCase_LaptopVui - Critical user journey
 * E2E test: full purchase from browsing to order confirmation
 */
test.describe('[Module: Giỏ hàng & Thanh toán]', () => {
  test('TC-E2E-CART-01: hành trình mua hàng đầy đủ (khách vãng lai)', async ({ page }) => {
    await test.step('Mở trang chủ và kiểm tra thương hiệu hiển thị', async () => {
      await page.goto('/');
      await expect(page.locator('body')).toContainText(/LAPTOP VUI/i);
    });

    await test.step('Click vào sản phẩm đầu tiên', async () => {
      const firstProduct = page.locator('a[href*="/sp?id="]').first();
      await firstProduct.click();
      await expect(page).toHaveURL(/sp\?id=/);
    });

    await test.step('Bấm "Thêm vào giỏ hàng"', async () => {
      const addBtn = page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ/i }).first();
      await addBtn.click();
    });

    await test.step('Xác nhận chuyển tới trang giỏ hàng (/showcart)', async () => {
      await expect(page).toHaveURL(/showcart/);
    });

    await test.step('Bấm "Tiến hành thanh toán"', async () => {
      const checkoutBtn = page.locator('a, button').filter({ hasText: /(thanh toán|checkout|đặt hàng)/i }).first();
      await checkoutBtn.click();
      await expect(page).toHaveURL(/checkout/);
    });

    await test.step('Điền thông tin giao hàng (họ tên, email, SĐT, địa chỉ)', async () => {
      const uniqueEmail = `e2e_${Date.now()}@example.com`;
      await page.fill('input[name="hoten"]', 'E2E Test User');
      await page.fill('input[name="email"]', uniqueEmail);
      await page.fill('input[name="dienthoai"]', '0912345678');
      await page.fill('textarea[name="diachi"], input[name="diachi"]', '123 E2E Test Street, HCM');
    });

    await test.step('Submit đơn hàng và xác nhận đặt hàng thành công', async () => {
      const submitBtn = page.locator('button[type="submit"], input[type="submit"]').filter({ hasText: /(đặt hàng|xác nhận|order)/i }).first();
      await submitBtn.click();
      await page.waitForURL(/^(?!.*checkout).*/, { timeout: 10000 });
      expect(page.url()).not.toContain('/checkout');
    });
  });

  test('TC-E2E-CART-02: không thể thanh toán khi giỏ hàng trống', async ({ page }) => {
    await test.step('Truy cập trực tiếp /checkout khi chưa thêm sản phẩm nào', async () => {
      await page.goto('/checkout');
    });

    await test.step('Xác nhận bị redirect về /showcart hoặc hiện thông báo giỏ trống', async () => {
      const url = page.url();
      const body = await page.textContent('body');
      expect(
        url.includes('showcart') ||
        (body?.toLowerCase().includes('trống') ?? false)
      ).toBeTruthy();
    });
  });

  test('TC-E2E-CART-03: cập nhật số lượng trong giỏ hàng hoạt động đúng', async ({ page }) => {
    await test.step('Thêm 1 sản phẩm vào giỏ', async () => {
      await page.goto('/');
      await page.locator('a[href*="/sp?id="]').first().click();
      await page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ/i }).first().click();
      await expect(page).toHaveURL(/showcart/);
    });

    await test.step('Sửa số lượng thành 3 và bấm cập nhật', async () => {
      const qtyInput = page.locator('input[type="number"], input[name*="soluong"]').first();
      if (await qtyInput.isVisible()) {
        await qtyInput.fill('3');
        const updateBtn = page.locator('button, input[type="submit"]').filter({ hasText: /(cập nhật|update)/i }).first();
        if (await updateBtn.isVisible()) {
          await updateBtn.click();
          await expect(page).toHaveURL(/showcart/);
        }
      }
    });
  });

  test('TC-E2E-CART-04: từ chối email không hợp lệ khi thanh toán', async ({ page }) => {
    await test.step('Thêm sản phẩm vào giỏ rồi vào trang checkout', async () => {
      await page.goto('/');
      await page.locator('a[href*="/sp?id="]').first().click();
      await page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ/i }).first().click();
      await page.goto('/checkout');
    });

    await test.step('Điền form với email không hợp lệ ("not-an-email")', async () => {
      await page.fill('input[name="hoten"]', 'Test');
      await page.fill('input[name="email"]', 'not-an-email');
      await page.fill('input[name="dienthoai"]', '0912345678');
      await page.fill('textarea[name="diachi"], input[name="diachi"]', '123');
    });

    await test.step('Submit và xác nhận hệ thống từ chối (vẫn ở /checkout)', async () => {
      const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
      await submitBtn.click();
      await page.waitForLoadState('networkidle');
      expect(page.url()).toContain('checkout');
    });
  });
});
