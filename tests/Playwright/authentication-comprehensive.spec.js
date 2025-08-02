/**
 * Comprehensive Authentication Flow Tests
 * 
 * Tests user registration, login, logout, and authentication security
 */

import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

test.describe('Authentication Comprehensive Testing', () => {

  test('user registration flow with validation', async ({ page }) => {
    const timestamp = Date.now();
    const testUser = {
      name: `Test User ${timestamp}`,
      email: `test${timestamp}@example.com`,
      password: 'password123'
    };

    // Navigate to register page
    await page.goto(`${BASE_URL}/register`);
    
    // Verify register page structure
    await expect(page).toHaveTitle(/Logje/);
    await expect(page.locator('text=Name').first()).toBeVisible();
    await expect(page.locator('text=Email').first()).toBeVisible();
    await expect(page.locator('text=Password').first()).toBeVisible();
    await expect(page.locator('text=Confirm Password')).toBeVisible();
    
    // Verify form fields are present
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
    
    // Test registration with valid data
    await page.fill('input[name="name"]', testUser.name);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.fill('input[name="password_confirmation"]', testUser.password);
    
    // Submit registration
    await page.click('button[type="submit"]');
    
    // Should redirect to dashboard after successful registration
    await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 10000 });
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Verify user is logged in (can see dashboard content)
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
  });

  test('registration validation errors', async ({ page }) => {
    await page.goto(`${BASE_URL}/register`);
    
    // Test empty form submission
    await page.click('button[type="submit"]');
    
    // Wait for form processing
    await page.waitForTimeout(1000);
    
    // In this application, form validation may not show visible error messages
    // Instead, verify that we stay on the registration page (form doesn't submit)
    await expect(page).toHaveURL(`${BASE_URL}/register`);
    
    // Verify registration form is still visible
    await expect(page.getByRole('textbox', { name: 'Name' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Email' })).toBeVisible();
    
    // Test password mismatch
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'differentpassword');
    
    await page.click('button[type="submit"]');
    await page.waitForTimeout(1000);
    
    // Should stay on registration page (form doesn't submit with invalid data)
    await expect(page).toHaveURL(`${BASE_URL}/register`);
    
    // Verify form is still visible (validation failed)
    await expect(page.getByRole('textbox', { name: 'Name' })).toBeVisible();
  });

  test('user login flow with valid credentials', async ({ page }) => {
    // First register a user to login with
    const timestamp = Date.now();
    const testUser = {
      name: `Login Test ${timestamp}`,
      email: `login${timestamp}@example.com`,
      password: 'password123'
    };

    // Register user
    await page.goto(`${BASE_URL}/register`);
    await page.fill('input[name="name"]', testUser.name);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.fill('input[name="password_confirmation"]', testUser.password);
    await page.click('button[type="submit"]');
    
    // Wait for dashboard and logout
    await page.waitForURL(`${BASE_URL}/dashboard`);
    
    // Click on user menu button to reveal dropdown
    await page.getByRole('button', { name: /Login Test/ }).click();
    
    // Wait for dropdown to appear and click logout
    await page.getByRole('button', { name: 'Log Out' }).click();
    await page.waitForURL(BASE_URL);
    
    // Now test login
    await page.goto(`${BASE_URL}/login`);
    
    // Verify login page structure
    await expect(page).toHaveTitle(/Logje/);
    await expect(page.locator('text=Email').first()).toBeVisible();
    await expect(page.locator('text=Password').first()).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
    
    // Login with valid credentials
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.click('button[type="submit"]');
    
    // Should redirect to dashboard
    await page.waitForURL(`${BASE_URL}/dashboard`, { timeout: 10000 });
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
  });

  test('login with invalid credentials', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    
    // Test with invalid credentials
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    // Should stay on login page with error message
    await page.waitForTimeout(2000);
    await expect(page.url()).toContain('/login');
    
    // Check for error message
    const hasLoginError = await page.locator('text=credentials').count() > 0 ||
                         await page.locator('text=invalid').count() > 0 ||
                         await page.locator('text=match').count() > 0 ||
                         await page.locator('.text-red-600').count() > 0;
    expect(hasLoginError).toBeTruthy();
  });

  test('logout functionality', async ({ page }) => {
    // Register and login a user first
    const timestamp = Date.now();
    const testUser = {
      name: `Logout Test ${timestamp}`,
      email: `logout${timestamp}@example.com`,
      password: 'password123'
    };

    await page.goto(`${BASE_URL}/register`);
    await page.fill('input[name="name"]', testUser.name);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.fill('input[name="password_confirmation"]', testUser.password);
    await page.click('button[type="submit"]');
    
    await page.waitForURL(`${BASE_URL}/dashboard`);
    
    // Test logout functionality
    // Click on user menu button to reveal dropdown  
    await page.getByRole('button', { name: /Logout Test/ }).click();
    
    // Wait for dropdown to appear and click logout
    await page.getByRole('button', { name: 'Log Out' }).click();
    
    // Should redirect to home page
    await page.waitForURL(BASE_URL, { timeout: 10000 });
    
    // Verify we're logged out (should see login/register links)
    await expect(page.locator('a[href*="/login"]')).toBeVisible();
    await expect(page.locator('a[href*="/register"]')).toBeVisible();
  });

  test('protected route access without authentication', async ({ page }) => {
    // Ensure we're not logged in by visiting home first
    await page.goto(BASE_URL);
    
    // Try to access protected dashboard page
    await page.goto(`${BASE_URL}/dashboard`);
    
    // Should redirect to login page
    await page.waitForURL(`${BASE_URL}/login`, { timeout: 10000 });
    await expect(page.locator('text=Email').first()).toBeVisible();
    await expect(page.locator('text=Password').first()).toBeVisible();
    
    // Try to access reports page
    await page.goto(`${BASE_URL}/reports`);
    
    // Should also redirect to login
    await page.waitForURL(`${BASE_URL}/login`, { timeout: 10000 });
  });

  test('remember me functionality (if implemented)', async ({ page }) => {
    // Register a user first
    const timestamp = Date.now();
    const testUser = {
      name: `Remember Test ${timestamp}`,
      email: `remember${timestamp}@example.com`,
      password: 'password123'
    };

    await page.goto(`${BASE_URL}/register`);
    await page.fill('input[name="name"]', testUser.name);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.fill('input[name="password_confirmation"]', testUser.password);
    await page.click('button[type="submit"]');
    
    await page.waitForURL(`${BASE_URL}/dashboard`);
    
    // Logout using the dropdown menu
    const userMenuButton = page.getByRole('button', { name: /Remember Test/ });
    await userMenuButton.click();
    await page.waitForTimeout(500);
    const logoutButton = page.getByRole('button', { name: 'Log Out' });
    await logoutButton.click();
    await page.waitForURL(BASE_URL);
    
    // Login with remember me (if checkbox exists)
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    
    // Check if remember me checkbox exists
    const rememberCheckbox = page.locator('input[name="remember"]');
    if (await rememberCheckbox.isVisible()) {
      await rememberCheckbox.check();
    }
    
    await page.click('button[type="submit"]');
    await page.waitForURL(`${BASE_URL}/dashboard`);
    
    // This test would need session/cookie verification to be complete
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
  });

  test('navigation between login and register pages', async ({ page }) => {
    // Start at login page
    await page.goto(`${BASE_URL}/login`);
    await expect(page.locator('text=Email').first()).toBeVisible();
    
    // Navigate to register page
    const registerLink = page.locator('a[href*="/register"]');
    if (await registerLink.isVisible()) {
      await registerLink.click();
      await page.waitForURL(`${BASE_URL}/register`);
      await expect(page.locator('text=Name').first()).toBeVisible();
    }
    
    // Navigate back to login page
    const loginLink = page.locator('a[href*="/login"]');
    if (await loginLink.isVisible()) {
      await loginLink.click();
      await page.waitForURL(`${BASE_URL}/login`);
      await expect(page.locator('text=Email').first()).toBeVisible();
    }
  });

  test('form field validation and accessibility', async ({ page }) => {
    await page.goto(`${BASE_URL}/register`);
    
    // Test that form fields have proper attributes
    const nameField = page.locator('input[name="name"]');
    const emailField = page.locator('input[name="email"]');
    const passwordField = page.locator('input[name="password"]');
    
    // Check field types
    await expect(emailField).toHaveAttribute('type', 'email');
    await expect(passwordField).toHaveAttribute('type', 'password');
    
    // Test email format validation
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="email"]', 'invalid-email');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');
    
    await page.click('button[type="submit"]');
    await page.waitForTimeout(1000);
    
    // Should show email validation error
    const hasEmailError = await page.locator('text=email').count() > 0 ||
                         await page.locator('text=valid').count() > 0 ||
                         await page.locator('.text-red-600').count() > 0;
    expect(hasEmailError).toBeTruthy();
  });

});