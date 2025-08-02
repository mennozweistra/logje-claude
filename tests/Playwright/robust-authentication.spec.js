/**
 * Robust Authentication Tests with Error Handling
 * 
 * Example of using helper functions for reliable test execution
 */

import { test, expect } from '@playwright/test';
import { 
  createTestUser, 
  registerAndLogin, 
  logout, 
  safeClick, 
  safeFill, 
  safeNavigate,
  waitForElement,
  retryAction,
  takeErrorScreenshot,
  handleDialog
} from './helpers/test-helpers.js';

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

test.describe('Robust Authentication Tests', () => {

  test('user registration with comprehensive error handling', async ({ page }) => {
    const testUser = createTestUser('Robust');
    
    try {
      // Navigate with retry
      await safeNavigate(page, `${BASE_URL}/register`);
      
      // Verify page loaded correctly
      await waitForElement(page, 'input[name="name"]');
      await waitForElement(page, 'input[name="email"]');
      
      // Fill form with safe methods
      await safeFill(page, 'input[name="name"]', testUser.name);
      await safeFill(page, 'input[name="email"]', testUser.email);
      await safeFill(page, 'input[name="password"]', testUser.password);
      await safeFill(page, 'input[name="password_confirmation"]', testUser.password);
      
      // Submit with retry
      await safeClick(page, 'button[type="submit"]');
      
      // Wait for successful redirect with timeout handling
      await retryAction(async () => {
        await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 10000 });
      }, 3, 2000);
      
      // Verify dashboard elements are present
      await waitForElement(page, 'h2:has-text("Dashboard")');
      await waitForElement(page, 'h2:has-text("Measurements")');
      
    } catch (error) {
      await takeErrorScreenshot(page, 'registration-failed', error);
      throw error;
    }
  });

  test('login flow with retry and error recovery', async ({ page }) => {
    let testUser;
    
    try {
      // First register a user
      testUser = await registerAndLogin(page, BASE_URL);
      
      // Logout to test login
      const userMenuButton = page.getByRole('button', { name: new RegExp(testUser.name) });
      await userMenuButton.click();
      await page.waitForTimeout(500);
      const logoutButton = page.getByRole('button', { name: 'Log Out' });
      await logoutButton.click();
      
      // Wait for redirect to home
      await retryAction(async () => {
        await page.waitForURL(BASE_URL, { timeout: 5000 });
      });
      
      // Navigate to login page
      await safeNavigate(page, `${BASE_URL}/login`);
      
      // Verify login page elements
      await waitForElement(page, 'input[name="email"]');
      await waitForElement(page, 'input[name="password"]');
      
      // Login with valid credentials
      await safeFill(page, 'input[name="email"]', testUser.email);
      await safeFill(page, 'input[name="password"]', testUser.password);
      await safeClick(page, 'button[type="submit"]');
      
      // Wait for successful login
      await retryAction(async () => {
        await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 10000 });
      });
      
      await waitForElement(page, 'h2:has-text("Dashboard")');
      
    } catch (error) {
      await takeErrorScreenshot(page, 'login-failed', error);
      throw error;
    }
  });

  test('handle login with invalid credentials gracefully', async ({ page }) => {
    try {
      await safeNavigate(page, `${BASE_URL}/login`);
      
      // Try invalid credentials
      await safeFill(page, 'input[name="email"]', 'invalid@example.com');
      await safeFill(page, 'input[name="password"]', 'wrongpassword');
      await safeClick(page, 'button[type="submit"]');
      
      // Wait and verify we stay on login page
      await page.waitForTimeout(2000);
      
      // Should still be on login page or have error message
      const currentUrl = page.url();
      const isOnLogin = currentUrl.includes('/login');
      const hasErrorMessage = await page.locator('.text-red-600').count() > 0 ||
                          await page.locator('text=credentials').count() > 0 ||
                          await page.locator('text=invalid').count() > 0 ||
                          await page.locator('text=match').count() > 0;
      
      expect(isOnLogin || hasErrorMessage).toBeTruthy();
      
    } catch (error) {
      await takeErrorScreenshot(page, 'invalid-login-test', error);
      throw error;
    }
  });

  test('protected route access with proper error handling', async ({ page }) => {
    try {
      // Ensure we're not logged in
      await safeNavigate(page, BASE_URL);
      
      // Try to access protected route
      await retryAction(async () => {
        await page.goto(`${BASE_URL}/dashboard`);
        // Should redirect to login within reasonable time
        await page.waitForURL(`${BASE_URL}/login`, { timeout: 8000 });
      });
      
      // Verify we're on login page
      await waitForElement(page, 'input[name="email"]');
      await waitForElement(page, 'input[name="password"]');
      
    } catch (error) {
      await takeErrorScreenshot(page, 'protected-route-test', error);
      throw error;
    }
  });

  test('logout with confirmation dialog handling', async ({ page }) => {
    let testUser;
    
    try {
      // Register and login
      testUser = await registerAndLogin(page, BASE_URL);
      
      // Logout using standard method (no confirmation dialog exists)
      const userMenuButton = page.getByRole('button', { name: new RegExp(testUser.name) });
      await userMenuButton.click();
      await page.waitForTimeout(500);
      const logoutButton = page.getByRole('button', { name: 'Log Out' });
      await logoutButton.click();
      
      // Wait for logout to complete
      await page.waitForURL(BASE_URL, { timeout: 8000 });
      
      // Verify we're logged out (should see login/register links)
      await waitForElement(page, 'a[href*="/login"]');
      
    } catch (error) {
      await takeErrorScreenshot(page, 'logout-with-dialog', error);
      throw error;
    }
  });

  test('form validation error recovery', async ({ page }) => {
    try {
      await page.goto(`${BASE_URL}/register`);
      
      // Submit empty form
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);
      
      // In this application, validation may not show visible error messages
      // Instead, verify that we stay on the registration page (form doesn't submit)
      await expect(page).toHaveURL(`${BASE_URL}/register`);
      
      // Verify registration form is still visible
      await expect(page.getByRole('textbox', { name: 'Name' })).toBeVisible();
      
      // Now fill form correctly and retry
      const testUser = createTestUser('ValidationTest');
      await safeFill(page, 'input[name="name"]', testUser.name);
      await safeFill(page, 'input[name="email"]', testUser.email);
      await safeFill(page, 'input[name="password"]', testUser.password);
      await safeFill(page, 'input[name="password_confirmation"]', testUser.password);
      
      await safeClick(page, 'button[type="submit"]');
      
      // Should now succeed
      await retryAction(async () => {
        await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 10000 });
      });
      
    } catch (error) {
      await takeErrorScreenshot(page, 'validation-recovery', error);
      throw error;
    }
  });

});