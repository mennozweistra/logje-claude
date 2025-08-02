/**
 * Dashboard Filter Functionality E2E Tests
 * 
 * Tests the measurement filtering, search, and UI interactions
 * Run with: npx playwright test tests/Playwright/DashboardFilterTest.js
 */

import { test, expect } from '@playwright/test';

// Test configuration
const BASE_URL = process.env.APP_URL || 'http://localhost:8000';
const TEST_USER = {
  email: 'test@example.com',
  password: 'password',
  name: 'Test User'
};

test.describe('Dashboard Filter Functionality', () => {
  
  test.beforeEach(async ({ page }) => {
    // Use existing seeded test user to see measurement data
    const testUser = {
      email: 'test@example.com',
      password: 'password'
    };

    // Login with seeded user
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', testUser.email);
    await page.fill('input[name="password"]', testUser.password);
    await page.click('button[type="submit"]');
    
    // Wait for redirect to dashboard
    await page.waitForURL(`${BASE_URL}/dashboard`);
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
  });

  test('dashboard loads and displays correctly', async ({ page }) => {
    // Verify page loads successfully (title is "Logje" which is the app name)
    await expect(page).toHaveTitle(/Logje/);
    
    // Verify dashboard elements are present
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
    await expect(page.locator('input[placeholder*="Search"]')).toBeVisible();
    
    // Verify filter elements are present
    await expect(page.locator('text=Measurement types')).toBeVisible();
    await expect(page.locator('input[type="checkbox"][value="glucose"]')).toBeVisible();
    await expect(page.locator('input[type="checkbox"][value="weight"]')).toBeVisible();
  });

  test('measurement type filters work correctly', async ({ page }) => {
    // Switch to detailed view to see individual measurements
    await page.getByText('Detailed').click();
    await expect(page.locator('text=Detailed')).toBeVisible();
    
    // Verify all measurement data is visible initially (seeded data)
    await expect(page.locator('text=107.00 mmol/L')).toBeVisible(); // Glucose data
    await expect(page.locator('text=74.10 kg')).toBeVisible(); // Weight data
    await expect(page.locator('text=52 minutes')).toBeVisible(); // Exercise data
    await expect(page.locator('text=Bike ride')).toBeVisible(); // Exercise description
    
    // Test Blood Glucose filter - check it to show only glucose
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    await page.waitForTimeout(1000);
    
    // Verify glucose data is still visible 
    await expect(page.locator('text=107.00 mmol/L')).toBeVisible();
    
    // Verify only glucose data is visible when filtered
    // Other data types should be hidden when glucose filter is active
    await expect(page.locator('text=74.10 kg')).not.toBeVisible(); // Weight should be hidden
    await expect(page.locator('text=52 minutes')).not.toBeVisible(); // Exercise should be hidden
    
    // Test Weight filter - uncheck glucose and check weight
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).uncheck();
    await page.getByRole('checkbox', { name: 'Weight' }).check();
    await page.waitForTimeout(1000);
    
    // Verify weight data is visible
    await expect(page.locator('text=74.10 kg')).toBeVisible();
    
    // Test Exercise filter
    await page.getByRole('checkbox', { name: 'Weight' }).uncheck();  
    await page.getByRole('checkbox', { name: 'Exercise' }).check();
    await page.waitForTimeout(1000);
    
    // Verify exercise data is visible
    await expect(page.locator('text=52 minutes')).toBeVisible();
    await expect(page.locator('text=Bike ride')).toBeVisible();
  });

  test('search functionality works', async ({ page }) => {
    // Test search functionality
    const searchInput = page.getByRole('textbox', { name: 'Search measurements' });
    await searchInput.fill('glucose');
    
    // Wait for any updates
    await page.waitForTimeout(1000);
    
    // Verify search input has value
    await expect(searchInput).toHaveValue('glucose');
    
    // Since no measurements exist, should still show empty state
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
    
    // Clear search
    await searchInput.fill('');
    await page.waitForTimeout(500);
    
    // Verify search is cleared
    await expect(searchInput).toHaveValue('');
  });

  test('date range filter works', async ({ page }) => {
    const dateSelect = page.getByRole('combobox', { name: 'Date range' });
    
    // Verify default selection
    await expect(dateSelect).toHaveValue('1');
    
    // Change date range to last 7 days
    await dateSelect.selectOption('7');
    await page.waitForTimeout(500);
    
    // Verify selection applied
    await expect(dateSelect).toHaveValue('7');
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
    
    // Change to last 30 days
    await dateSelect.selectOption('30');
    await page.waitForTimeout(500);
    
    // Verify selection applied
    await expect(dateSelect).toHaveValue('30');
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
  });

  test('view toggle functionality', async ({ page }) => {
    // Verify we can see both Summary and Detailed options
    await expect(page.locator('text=Summary')).toBeVisible();
    await expect(page.locator('text=Detailed')).toBeVisible();
    
    // Test clicking the detailed view toggle
    await page.getByText('Detailed').click();
    await expect(page.locator('text=Detailed')).toBeVisible();
    
    // Test clicking back to summary view
    await page.getByText('Summary').click();
    await expect(page.locator('text=Summary')).toBeVisible();
    
    // Both options should still be visible (the view changes but options remain)
    await expect(page.locator('text=Summary')).toBeVisible();
    await expect(page.locator('text=Detailed')).toBeVisible();
  });

  test('manual filter reset functionality', async ({ page }) => {
    // Apply some filters
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    await page.getByRole('textbox', { name: 'Search measurements' }).fill('test');
    await page.getByRole('combobox', { name: 'Date range' }).selectOption('30');
    
    // Wait for updates
    await page.waitForTimeout(500);
    
    // Verify filters are applied
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toHaveValue('test');
    await expect(page.getByRole('combobox', { name: 'Date range' })).toHaveValue('30');
    
    // Manually reset filters (no clear all button exists)
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).uncheck();
    await page.getByRole('textbox', { name: 'Search measurements' }).fill('');
    await page.getByRole('combobox', { name: 'Date range' }).selectOption('1');
    
    await page.waitForTimeout(500);
    
    // Verify filters are cleared
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).not.toBeChecked();
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toHaveValue('');
    await expect(page.getByRole('combobox', { name: 'Date range' })).toHaveValue('1');
  });

  test('responsive design works on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Verify mobile navigation works
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
    
    // Test filter functionality on mobile
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    await page.waitForTimeout(500);
    
    // Verify filter works on mobile
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
  });

  test('navigation between pages works', async ({ page }) => {
    // Navigate to Reports using proper selector
    await page.getByRole('link', { name: 'Reports' }).first().click();
    await page.waitForURL(`${BASE_URL}/reports`);
    
    // Verify reports page loads
    await expect(page.getByRole('heading', { name: 'Reports & Analytics' })).toBeVisible();
    
    // Navigate back to Dashboard
    await page.getByRole('link', { name: 'Dashboard' }).first().click();
    await page.waitForURL(`${BASE_URL}/dashboard`);
    
    // Verify dashboard loads
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
  });

});

test.describe('Error Handling', () => {
  
  test('handles unauthenticated access', async ({ page }) => {
    // Try to access dashboard without logging in
    await page.goto(`${BASE_URL}/dashboard`);
    
    // Should redirect to login
    await page.waitForURL(`${BASE_URL}/login`);
    
    // Verify login page elements
    await expect(page.getByRole('textbox', { name: 'Email' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Password' })).toBeVisible();
  });

});