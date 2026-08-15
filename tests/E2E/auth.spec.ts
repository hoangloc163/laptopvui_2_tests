import { test, expect } from '@playwright/test';

/**
 * @module Đăng ký / Đăng nhập
 * @source BaoCao_TestCase_LaptopVui mục 5.2.6 - Auth
 */
test.describe('[Module: Đăng ký / Đăng nhập]', () => {
  test('TC-E2E-AUTH-01: đăng ký tài khoản mới thành công', async ({ page }) => {
    const uniqueEmail = `e2e_reg_${Date.now()}@example.com`;

    await test.step('Mở trang đăng ký', async () => {
      await page.goto('/register');
    });

    await test.step('Điền form đăng ký hợp lệ (họ tên, email, mật khẩu)', async () => {
      await page.fill('input[name="hoten"]', 'E2E Test User');
      await page.fill('input[name="email"]', uniqueEmail);
      await page.fill('input[name="matkhau"]', 'password123');
    });

    await test.step('Submit và xác nhận redirect sang /login', async () => {
      const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
      await submitBtn.click();
      await page.waitForURL(/login/, { timeout: 10000 });
      expect(page.url()).toContain('login');
    });
  });

  test('TC-E2E-AUTH-02: hành trình đầy đủ đăng ký rồi đăng nhập', async ({ page }) => {
    const uniqueEmail = `e2e_full_${Date.now()}@example.com`;

    await test.step('Đăng ký tài khoản mới', async () => {
      await page.goto('/register');
      await page.fill('input[name="hoten"]', 'Full Journey Test');
      await page.fill('input[name="email"]', uniqueEmail);
      await page.fill('input[name="matkhau"]', 'password123');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
      await page.waitForURL(/login/);
    });

    await test.step('Đăng nhập bằng tài khoản vừa tạo', async () => {
      await page.fill('input[name="email"]', uniqueEmail);
      await page.fill('input[name="matkhau"]', 'password123');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
    });

    await test.step('Xác nhận redirect về trang chủ (đăng nhập thành công)', async () => {
      await page.waitForURL('/', { timeout: 10000 });
      expect(page.url()).not.toContain('/login');
    });
  });

  test('TC-E2E-AUTH-03: từ chối mật khẩu quá ngắn', async ({ page }) => {
    await test.step('Mở form đăng ký và điền mật khẩu ngắn (5 ký tự)', async () => {
      await page.goto('/register');
      await page.fill('input[name="hoten"]', 'Short Pass');
      await page.fill('input[name="email"]', `short_${Date.now()}@example.com`);
      await page.fill('input[name="matkhau"]', '12345');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
    });

    await test.step('Xác nhận form không cho qua (vẫn ở /register)', async () => {
      await page.waitForLoadState('networkidle');
      expect(page.url()).toContain('register');
    });
  });

  test('TC-E2E-AUTH-04: từ chối email trùng lặp', async ({ page }) => {
    const email = `dup_e2e_${Date.now()}@example.com`;

    await test.step('Đăng ký lần 1 với email X', async () => {
      await page.goto('/register');
      await page.fill('input[name="hoten"]', 'First');
      await page.fill('input[name="email"]', email);
      await page.fill('input[name="matkhau"]', 'password123');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
    });

    await test.step('Đăng ký lần 2 với cùng email X', async () => {
      await page.goto('/register');
      await page.fill('input[name="hoten"]', 'Second');
      await page.fill('input[name="email"]', email);
      await page.fill('input[name="matkhau"]', 'password456');
      await page.locator('button[type="submit"], input[type="submit"]').first().click();
      await page.waitForLoadState('networkidle');
    });

    await test.step('Xác nhận hệ thống từ chối (vẫn ở /register)', async () => {
      expect(page.url()).toContain('register');
    });
  });
});
