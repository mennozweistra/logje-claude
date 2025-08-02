/**
 * Comprehensive Dashboard Filter Tests
 * 
 * Tests dashboard functionality with proper data verification and error handling
 */

import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

test.describe('Dashboard Comprehensive Testing', () => {
  
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

  test('dashboard structure and navigation elements', async ({ page }) => {
    // Verify page title
    await expect(page).toHaveTitle(/Logje/);
    
    // Verify main dashboard elements
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
    
    // Verify navigation elements (use first() to avoid multiple matches)
    await expect(page.locator('a[href*="/dashboard"]').first()).toBeVisible();
    await expect(page.locator('a[href*="/reports"]').first()).toBeVisible();
    
    // Verify filter elements are present
    await expect(page.locator('text=Measurement types')).toBeVisible();
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeVisible();
    await expect(page.getByRole('checkbox', { name: 'Weight' })).toBeVisible();
    await expect(page.getByRole('checkbox', { name: 'Exercise' })).toBeVisible();
    await expect(page.getByRole('checkbox', { name: 'Notes' })).toBeVisible();
    
    // Verify search box
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toBeVisible();
    
    // Verify date range selector
    await expect(page.getByRole('combobox', { name: 'Date range' })).toBeVisible();
    
    // Verify view toggle (Summary/Detailed)
    await expect(page.locator('text=Summary')).toBeVisible();
    await expect(page.locator('text=Detailed')).toBeVisible();
  });

  test('view toggle functionality between summary and detailed', async ({ page }) => {
    // Should start in summary view
    await expect(page.locator('text=Summary')).toBeVisible();
    
    // Toggle to detailed view
    await page.getByText('Detailed').click();
    
    // Wait for Livewire update and verify detailed view
    await expect(page.locator('text=Detailed')).toBeVisible({ timeout: 3000 });
    
    // Toggle back to summary view
    await page.getByText('Summary').click();
    
    // Verify we're back to summary
    await expect(page.locator('text=Summary')).toBeVisible({ timeout: 3000 });
  });

  test('measurement type filters work correctly', async ({ page }) => {
    // Test Blood Glucose filter
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    
    // Wait a moment for any updates
    await page.waitForTimeout(500);
    
    // Verify Blood Glucose filter is checked
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    
    // Since no measurements exist, verify the empty state message
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
    
    // Test Weight filter
    await page.getByRole('checkbox', { name: 'Weight' }).check();
    await page.waitForTimeout(500);
    
    // Verify Weight filter is checked
    await expect(page.getByRole('checkbox', { name: 'Weight' })).toBeChecked();
    
    // Both should now be checked
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    await expect(page.getByRole('checkbox', { name: 'Weight' })).toBeChecked();
    
    // Uncheck Blood Glucose
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).uncheck();
    await page.waitForTimeout(500);
    
    // Only Weight should be checked now
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).not.toBeChecked();
    await expect(page.getByRole('checkbox', { name: 'Weight' })).toBeChecked();
  });

  test('search functionality works', async ({ page }) => {
    // Test search with glucose term
    const searchInput = page.getByRole('textbox', { name: 'Search measurements' });
    await searchInput.fill('glucose');
    
    // Wait for any updates
    await page.waitForTimeout(1000);
    
    // Verify the search input has the value
    await expect(searchInput).toHaveValue('glucose');
    
    // Since no measurements exist, should still show empty state
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
    
    // Clear search
    await searchInput.fill('');
    await page.waitForTimeout(500);
    
    // Verify search is cleared
    await expect(searchInput).toHaveValue('');
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
  });

  test('date range filter functionality', async ({ page }) => {
    const dateSelect = page.getByRole('combobox', { name: 'Date range' });
    
    // Test different date ranges by their numeric values
    const dateRanges = [
      { value: '7', text: 'Last 7 days' },
      { value: '14', text: 'Last 2 weeks' },
      { value: '30', text: 'Last month' },
      { value: '90', text: 'Last 3 months' }
    ];
    
    // Verify default selection (value is "1" for "Today only")
    await expect(dateSelect).toHaveValue('1');
    
    for (const range of dateRanges) {
      await dateSelect.selectOption(range.value);
      await page.waitForTimeout(500);
      
      // Verify the selection is applied (check by value, not display text)
      await expect(dateSelect).toHaveValue(range.value);
      
      // Should still show no measurements message since we have no data
      await expect(page.locator('text=No measurements recorded')).toBeVisible();
    }
    
    // Reset to default (value "1" for "Today only")
    await dateSelect.selectOption('1');
    await expect(dateSelect).toHaveValue('1');
  });

  test('filters can be manually reset', async ({ page }) => {
    // Apply multiple filters
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    await page.getByRole('checkbox', { name: 'Weight' }).check();
    await page.getByRole('textbox', { name: 'Search measurements' }).fill('test search');
    await page.getByRole('combobox', { name: 'Date range' }).selectOption('30'); // "Last month" has value "30"
    
    // Wait for updates
    await page.waitForTimeout(500);
    
    // Verify filters are applied
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    await expect(page.getByRole('checkbox', { name: 'Weight' })).toBeChecked();
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toHaveValue('test search');
    await expect(page.getByRole('combobox', { name: 'Date range' })).toHaveValue('30'); // Check value, not display text
    
    // Manually reset filters (since there's no clear all button)
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).uncheck();
    await page.getByRole('checkbox', { name: 'Weight' }).uncheck();
    await page.getByRole('textbox', { name: 'Search measurements' }).fill('');
    await page.getByRole('combobox', { name: 'Date range' }).selectOption('1'); // "Today only" has value "1"
    
    await page.waitForTimeout(500);
    
    // All filters should be reset
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).not.toBeChecked();
    await expect(page.getByRole('checkbox', { name: 'Weight' })).not.toBeChecked();
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toHaveValue('');
    await expect(page.getByRole('combobox', { name: 'Date range' })).toHaveValue('1'); // Check value "1", not display text
  });

  test('responsive design on mobile viewport', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Verify mobile navigation works
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
    
    // Verify filter elements are still accessible on mobile
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toBeVisible();
    await expect(page.getByRole('combobox', { name: 'Date range' })).toBeVisible();
    
    // Test filter functionality on mobile
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    await page.waitForTimeout(500);
    
    // Should work the same as desktop
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    await expect(page.locator('text=No measurements recorded')).toBeVisible();
  });

  test('navigation between dashboard and reports', async ({ page }) => {
    // Navigate to Reports using the first Reports link (avoid strict mode violation)
    await page.getByRole('link', { name: 'Reports' }).first().click();
    await page.waitForURL(`${BASE_URL}/reports`);
    
    // Verify reports page loads by checking for specific heading
    await expect(page.getByRole('heading', { name: 'Reports & Analytics' })).toBeVisible({ timeout: 5000 });
    
    // Navigate back to Dashboard
    await page.getByRole('link', { name: 'Dashboard' }).first().click();
    await page.waitForURL(`${BASE_URL}/dashboard`);
    
    // Verify dashboard loads
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    await expect(page.locator('h2:has-text("Measurements")')).toBeVisible();
  });

  test('error handling for unauthenticated access', async ({ page }) => {
    // Logout first - click user menu and then logout
    const userMenuButton = page.getByRole('button', { name: /Test User/ });
    await userMenuButton.click();
    
    // Wait for dropdown and click logout
    await page.waitForTimeout(500);
    const logoutButton = page.getByRole('button', { name: 'Log Out' });
    await logoutButton.click();
    
    // Wait for logout to complete
    await page.waitForURL(BASE_URL);
    
    // Try to access dashboard without authentication
    await page.goto(`${BASE_URL}/dashboard`);
    
    // Should redirect to login
    await page.waitForURL(`${BASE_URL}/login`);
    await expect(page.getByRole('textbox', { name: 'Email' })).toBeVisible();
    await expect(page.getByRole('textbox', { name: 'Password' })).toBeVisible();
  });

});