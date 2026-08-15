import { test, expect } from '@playwright/test';

/**
 * @module Admin
 * @source BaoCao_TestCase_LaptopVui mục 5.2.7/5.2.8/5.2.9 - Admin
 */

async function loginAsAdmin(page: import('@playwright/test').Page) {
  await page.goto('/admin/login');
  await page.fill('input[name="email"]', 'admin@demo.local');
  await page.fill('input[name="matkhau"]', 'admin123');
  await page.locator('button[type="submit"], input[type="submit"]').first().click();
  await page.waitForURL(/\/admin(\/|$|\?)/, { timeout: 10000 });
}

test.describe('[Module: Admin]', () => {
  test('TC-E2E-ADMIN-01: chưa đăng nhập bị redirect về trang đăng nhập admin', async ({ page }) => {
    await test.step('Truy cập /admin khi chưa đăng nhập', async () => {
      await page.goto('/admin');
    });

    await test.step('Xác nhận bị redirect sang /admin/login', async () => {
      await page.waitForURL(/admin\/login/, { timeout: 5000 });
      expect(page.url()).toContain('/admin/login');
    });
  });

  test('TC-E2E-ADMIN-02: đăng nhập admin thành công với thông tin hợp lệ', async ({ page }) => {
    await test.step('Điền email/mật khẩu admin hợp lệ và submit', async () => {
      await page.goto('/admin/login');
      await page.fill('input[name="email"]', 'admin@demo.local');
      await page.fill('input[name="matkhau"]', 'admin123');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
    });

    await test.step('Xác nhận redirect vào dashboard /admin', async () => {
      await page.waitForURL(/\/admin(\/|$|\?)/, { timeout: 10000 });
      expect(page.url()).toContain('/admin');
      expect(page.url()).not.toContain('/admin/login');
    });
  });

  test('TC-E2E-ADMIN-03: dashboard hiển thị số liệu thống kê', async ({ page }) => {
    await test.step('Đăng nhập admin', async () => {
      await loginAsAdmin(page);
    });

    await test.step('Kiểm tra dashboard có thẻ thống kê (sản phẩm/đơn hàng/danh mục/doanh thu)', async () => {
      const body = await page.textContent('body');
      expect(body?.toLowerCase()).toMatch(/(sản phẩm|đơn hàng|danh mục|doanh thu)/);
    });
  });

  test('TC-E2E-ADMIN-04: điều hướng tới danh sách sản phẩm', async ({ page }) => {
    await test.step('Đăng nhập admin', async () => {
      await loginAsAdmin(page);
    });

    await test.step('Mở /admin/sp và kiểm tra trang danh sách sản phẩm load đúng', async () => {
      await page.goto('/admin/sp');
      await expect(page.locator('body')).toContainText(/(sản phẩm|product)/i);
    });
  });

  test('TC-E2E-ADMIN-05: đăng nhập admin sai mật khẩu bị từ chối', async ({ page }) => {
    await test.step('Điền email đúng nhưng mật khẩu sai và submit', async () => {
      await page.goto('/admin/login');
      await page.fill('input[name="email"]', 'admin@demo.local');
      await page.fill('input[name="matkhau"]', 'wrongpassword');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
    });

    await test.step('Xác nhận vẫn ở lại /admin/login (đăng nhập thất bại)', async () => {
      await page.waitForLoadState('networkidle');
      expect(page.url()).toContain('/admin/login');
    });
  });
});
