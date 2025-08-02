/**
 * Authentication Flow E2E Tests
 * 
 * Tests user registration, login, logout flows
 */

import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

test.describe('Authentication Flow', () => {
  
  test('user can register and login', async ({ page }) => {
    const timestamp = Date.now();
    const testUser = {
      name: `Test User ${timestamp}`,
      email: `test${timestamp}@example.com`,
      password: 'password123'
    };

    // Navigate to register page
    await page.goto(`${BASE_URL}/register`);
    
    // Verify register page loads
    await expect(page.locator('text=Name')).toBeVisible();
    await expect(page.locator('text=Email')).toBeVisible();
    
    // Fill registration form
    await page.fill('input[name="name"]', testUser.name);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.fill('input[name="password_confirmation"]', testUser.password);
    
    // Submit registration
    await page.click('button[type="submit"]');
    
    // Should redirect to dashboard
    await page.waitForURL(`${BASE_URL}/dashboard`);
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Take screenshot of successful registration
    await page.screenshot({ path: 'tests/Playwright/screenshots/registration-success.png' });
    
    // Logout using proper dropdown menu
    const userMenuButton = page.getByRole('button', { name: new RegExp(testUser.name) });
    await userMenuButton.click();
    await page.waitForTimeout(500);
    const logoutButton = page.getByRole('button', { name: 'Log Out' });
    await logoutButton.click();
    
    // Should redirect to home
    await page.waitForURL(BASE_URL);
    
    // Now test login with the same credentials
    await page.goto(`${BASE_URL}/login`);
    
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    
    await page.click('button[type="submit"]');
    
    // Should redirect to dashboard
    await page.waitForURL(`${BASE_URL}/dashboard`);
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Take screenshot of successful login
    await page.screenshot({ path: 'tests/Playwright/screenshots/login-success.png' });
  });

  test('handles invalid login credentials', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    
    // Try with invalid credentials
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    
    await page.click('button[type="submit"]');
    
    // Should stay on login page with error
    await expect(page.locator('text=These credentials do not match our records')).toBeVisible();
    
    // Take screenshot of error state
    await page.screenshot({ path: 'tests/Playwright/screenshots/login-error.png' });
  });

  test('redirects unauthenticated users', async ({ page }) => {
    // Try to access protected dashboard page
    await page.goto(`${BASE_URL}/dashboard`);
    
    // Should redirect to login
    await page.waitForURL(`${BASE_URL}/login`);
    await expect(page.locator('text=Email')).toBeVisible();
    
    // Try to access reports page
    await page.goto(`${BASE_URL}/reports`);
    
    // Should redirect to login
    await page.waitForURL(`${BASE_URL}/login`);
  });

});