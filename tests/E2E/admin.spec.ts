import { test, expect } from '@playwright/test';

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.7 - Admin
 */
test.describe('Admin', () => {
  test('TC-E2E-ADMIN-01: unauthenticated redirects to admin login', async ({ page }) => {
    await page.goto('/admin');
    // Should be redirected to /admin/login
    await page.waitForURL(/admin\/login/, { timeout: 5000 });
    expect(page.url()).toContain('/admin/login');
  });

  test('TC-E2E-ADMIN-02: admin login with valid credentials', async ({ page }) => {
    await page.goto('/admin/login');

    await page.fill('input[name="email"]', 'admin@demo.local');
    await page.fill('input[name="matkhau"]', 'admin123');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();

    // Should redirect to /admin dashboard
    await page.waitForURL(/\/admin(\/|$|\?)/, { timeout: 10000 });
    expect(page.url()).toContain('/admin');
    expect(page.url()).not.toContain('/admin/login');
  });

  test('TC-E2E-ADMIN-03: dashboard shows stats', async ({ page }) => {
    // Login first
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@demo.local');
    await page.fill('input[name="matkhau"]', 'admin123');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();
    await page.waitForURL(/\/admin(\/|$|\?)/);

    // Dashboard should have stat cards
    const body = await page.textContent('body');
    expect(body?.toLowerCase()).toMatch(/(sản phẩm|đơn hàng|danh mục|doanh thu)/);
  });

  test('TC-E2E-ADMIN-04: navigate to product list', async ({ page }) => {
    // Login
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@demo.local');
    await page.fill('input[name="matkhau"]', 'admin123');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();
    await page.waitForURL(/\/admin(\/|$|\?)/);

    // Navigate to product list
    await page.goto('/admin/sp');
    await expect(page.locator('body')).toContainText(/(sản phẩm|product)/i);
  });

  test('TC-E2E-ADMIN-05: admin login rejects wrong password', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@demo.local');
    await page.fill('input[name="matkhau"]', 'wrongpassword');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();

    // Should stay on /admin/login
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('/admin/login');
  });
});
