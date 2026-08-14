import { test, expect } from '@playwright/test';

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.6 - Auth
 */
test.describe('Authentication', () => {
  test('TC-E2E-AUTH-01: register new user successfully', async ({ page }) => {
    await page.goto('/register');

    const uniqueEmail = `e2e_reg_${Date.now()}@example.com`;
    await page.fill('input[name="hoten"]', 'E2E Test User');
    await page.fill('input[name="email"]', uniqueEmail);
    await page.fill('input[name="matkhau"]', 'password123');

    const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
    await submitBtn.click();

    // Should redirect to /login
    await page.waitForURL(/login/, { timeout: 10000 });
    expect(page.url()).toContain('login');
  });

  test('TC-E2E-AUTH-02: register + login journey', async ({ page }) => {
    // Register
    const uniqueEmail = `e2e_full_${Date.now()}@example.com`;
    await page.goto('/register');
    await page.fill('input[name="hoten"]', 'Full Journey Test');
    await page.fill('input[name="email"]', uniqueEmail);
    await page.fill('input[name="matkhau"]', 'password123');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();

    // On login page now
    await page.waitForURL(/login/);

    // Login with credentials just created
    await page.fill('input[name="email"]', uniqueEmail);
    await page.fill('input[name="matkhau"]', 'password123');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();

    // Should redirect to home
    await page.waitForURL('/', { timeout: 10000 });
    expect(page.url()).not.toContain('/login');
  });

  test('TC-E2E-AUTH-03: reject short password', async ({ page }) => {
    await page.goto('/register');
    await page.fill('input[name="hoten"]', 'Short Pass');
    await page.fill('input[name="email"]', `short_${Date.now()}@example.com`);
    await page.fill('input[name="matkhau"]', '12345');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();

    // Should stay on register or redirect back with error
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('register');
  });

  test('TC-E2E-AUTH-04: reject duplicate email', async ({ page }) => {
    const email = `dup_e2e_${Date.now()}@example.com`;

    // Register first
    await page.goto('/register');
    await page.fill('input[name="hoten"]', 'First');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="matkhau"]', 'password123');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();
    await page.waitForLoadState('networkidle');

    // Try again with same email
    await page.goto('/register');
    await page.fill('input[name="hoten"]', 'Second');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="matkhau"]', 'password456');
    await page.locator('button[type="submit"], input[type="submit"]').first().click();

    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('register');
  });
});
