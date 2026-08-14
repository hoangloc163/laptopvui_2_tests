import { test, expect } from '@playwright/test';

/**
 * @source BaoCao_TestCase_LaptopVui - Critical user journey
 * E2E test: full purchase from browsing to order confirmation
 */
test.describe('Cart & Checkout Journey', () => {
  test('TC-E2E-CART-01: complete purchase flow as guest', async ({ page }) => {
    // 1. Land on homepage
    await page.goto('/');
    await expect(page.locator('body')).toContainText(/LAPTOP VUI/i);

    // 2. Click first product
    const firstProduct = page.locator('a[href*="/sp?id="]').first();
    await firstProduct.click();
    await expect(page).toHaveURL(/sp\?id=/);

    // 3. Add to cart
    const addBtn = page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ/i }).first();
    await addBtn.click();

    // 4. Should land on cart page
    await expect(page).toHaveURL(/showcart/);

    // 5. Click "Tiến hành thanh toán" (Proceed to checkout)
    const checkoutBtn = page.locator('a, button').filter({ hasText: /(thanh toán|checkout|đặt hàng)/i }).first();
    await checkoutBtn.click();

    // 6. Should be on checkout page
    await expect(page).toHaveURL(/checkout/);

    // 7. Fill checkout form
    const uniqueEmail = `e2e_${Date.now()}@example.com`;
    await page.fill('input[name="hoten"]', 'E2E Test User');
    await page.fill('input[name="email"]', uniqueEmail);
    await page.fill('input[name="dienthoai"]', '0912345678');
    await page.fill('textarea[name="diachi"], input[name="diachi"]', '123 E2E Test Street, HCM');

    // 8. Submit
    const submitBtn = page.locator('button[type="submit"], input[type="submit"]').filter({ hasText: /(đặt hàng|xác nhận|order)/i }).first();
    await submitBtn.click();

    // 9. Should redirect (success)
    await page.waitForURL(/^(?!.*checkout).*/, { timeout: 10000 });
    const url = page.url();
    expect(url).not.toContain('/checkout');
  });

  test('TC-E2E-CART-02: cannot checkout with empty cart', async ({ page }) => {
    // Direct access to /checkout without adding anything
    await page.goto('/checkout');

    // Should redirect to /showcart or show empty state
    const url = page.url();
    const body = await page.textContent('body');
    expect(
      url.includes('showcart') ||
      (body?.toLowerCase().includes('trống') ?? false)
    ).toBeTruthy();
  });

  test('TC-E2E-CART-03: update cart quantity works', async ({ page }) => {
    // Add product
    await page.goto('/');
    await page.locator('a[href*="/sp?id="]').first().click();
    await page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ/i }).first().click();

    // On cart page - find quantity input
    await expect(page).toHaveURL(/showcart/);
    const qtyInput = page.locator('input[type="number"], input[name*="soluong"]').first();
    if (await qtyInput.isVisible()) {
      await qtyInput.fill('3');
      // Submit update
      const updateBtn = page.locator('button, input[type="submit"]').filter({ hasText: /(cập nhật|update)/i }).first();
      if (await updateBtn.isVisible()) {
        await updateBtn.click();
        // After update, page should reload cart
        await expect(page).toHaveURL(/showcart/);
      }
    }
  });

  test('TC-E2E-CART-04: reject invalid email at checkout', async ({ page }) => {
    // Add product then go to checkout
    await page.goto('/');
    await page.locator('a[href*="/sp?id="]').first().click();
    await page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ/i }).first().click();
    await page.goto('/checkout');

    // Fill with invalid email
    await page.fill('input[name="hoten"]', 'Test');
    await page.fill('input[name="email"]', 'not-an-email');
    await page.fill('input[name="dienthoai"]', '0912345678');
    await page.fill('textarea[name="diachi"], input[name="diachi"]', '123');

    const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
    await submitBtn.click();

    // Should stay on /checkout or redirect back to /checkout with error
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('checkout');
  });
});
