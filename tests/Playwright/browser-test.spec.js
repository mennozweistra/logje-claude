/**
 * Simple Browser Test for UI Mode Verification
 * 
 * This test is designed to stay open longer to verify browser control
 */

import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

test.describe('Browser Control Verification', () => {
  
  test('verify browser opens correctly and stays visible', async ({ page }) => {
    // Navigate to home page
    await page.goto(BASE_URL);
    
    // Verify page loads
    await expect(page).toHaveTitle(/Laravel/);
    
    // Wait 3 seconds to allow visual verification of correct browser
    await page.waitForTimeout(3000);
    
    // Take a screenshot for verification
    await page.screenshot({ path: 'tests/Playwright/screenshots/browser-verification.png' });
    
    // Navigate to login to verify functionality
    await page.click('a[href*="/login"]');
    await expect(page.locator('text=Email')).toBeVisible();
    
    // Wait another 2 seconds
    await page.waitForTimeout(2000);
  });

});