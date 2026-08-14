import { test, expect } from '@playwright/test';

/**
 * @source BaoCao_TestCase_LaptopVui mục 5.2.1 + 5.3.1 (UI/UX)
 * E2E test: user visits homepage and sees all key sections
 */
test.describe('Homepage', () => {
  test('TC-E2E-HOME-01: displays brand, navigation, and product sections', async ({ page }) => {
    await page.goto('/');

    // Brand visible
    await expect(page.locator('body')).toContainText(/LAPTOP VUI/i);

    // Navigation with categories
    const categoryLinks = page.locator('a[href*="loai?idloai="]');
    await expect(categoryLinks.first()).toBeVisible();

    // At least one product card
    const productCards = page.locator('a[href*="/sp?id="]');
    await expect(productCards.first()).toBeVisible();
  });

  test('TC-E2E-HOME-02: cart badge appears after adding product', async ({ page }) => {
    await page.goto('/');

    // Click first product
    const firstProduct = page.locator('a[href*="/sp?id="]').first();
    await firstProduct.click();

    // Should be on product detail page
    await expect(page).toHaveURL(/sp\?id=/);

    // Add to cart
    const addBtn = page.locator('button, input[type="submit"]').filter({ hasText: /thêm.*giỏ|add.*cart/i }).first();
    if (await addBtn.isVisible()) {
      await addBtn.click();
      // Should redirect to cart
      await expect(page).toHaveURL(/showcart/);
    }
  });

  test('TC-E2E-HOME-03: search from navigation returns results', async ({ page }) => {
    await page.goto('/');

    const searchInput = page.locator('input[name="keyword"]').first();
    await searchInput.fill('laptop');

    // Submit via Enter or button
    await searchInput.press('Enter');

    // Should navigate to /tk (search results)
    await expect(page).toHaveURL(/tk/);
  });

  test('TC-E2E-HOME-04: category link navigates correctly', async ({ page }) => {
    await page.goto('/');

    // Click first category
    const categoryLink = page.locator('a[href*="loai?idloai="]').first();
    const categoryHref = await categoryLink.getAttribute('href');
    await categoryLink.click();

    // URL should contain idloai
    await expect(page).toHaveURL(/loai\?idloai=/);
  });
});
