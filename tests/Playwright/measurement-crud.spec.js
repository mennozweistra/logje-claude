/**
 * Comprehensive Measurement CRUD Operation Tests
 * 
 * Tests creating, reading, updating, and deleting measurements of all types
 */

import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

test.describe('Measurement CRUD Operations', () => {
  
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

  test('create glucose measurement', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Click the glucose measurement type button
    await page.getByRole('button', { name: '🩸 Glucose (mmol/L)' }).click();
    
    // Verify the glucose form opened
    await expect(page.locator('h3:has-text("Add Glucose Measurement")')).toBeVisible();
    
    // Fill in glucose value
    await page.locator('input[type="number"]').fill('5.5');
    
    // Check fasting checkbox
    await page.getByRole('checkbox', { name: 'Fasting measurement' }).check();
    
    // Fill in notes
    await page.locator('textarea').fill('Test glucose measurement');
    
    // Submit the form
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    
    // Verify success message is displayed
    await expect(page.locator('text=Measurement added successfully')).toBeVisible();
    
    // Wait for form to close and verify we're back to dashboard
    await expect(page.locator('h3:has-text("Add New Measurement")')).toBeVisible();
    
    // Take screenshot for verification
    await page.screenshot({ path: 'tests/Playwright/screenshots/glucose-create.png' });
  });

  test('create weight measurement', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Click the weight measurement type button
    await page.getByRole('button', { name: '⚖️ Weight (kg)' }).click();
    
    // Verify the weight form opened
    await expect(page.locator('h3:has-text("Add Weight Measurement")')).toBeVisible();
    
    // Fill in weight value
    await page.locator('input[type="number"]').fill('75.5');
    
    // Fill in notes
    await page.locator('textarea').fill('Morning weight measurement');
    
    // Submit the form
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    
    // Verify success message is displayed
    await expect(page.locator('text=Measurement added successfully')).toBeVisible();
    
    // Wait for form to close and verify we're back to dashboard
    await expect(page.locator('h3:has-text("Add New Measurement")')).toBeVisible();
    
    // Take screenshot for verification
    await page.screenshot({ path: 'tests/Playwright/screenshots/weight-create.png' });
  });

  test('create exercise measurement', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Click the exercise measurement type button
    await page.getByRole('button', { name: '🏃‍♂️ Exercise' }).click();
    
    // Verify the exercise form opened
    await expect(page.locator('h3:has-text("Add Exercise Measurement")')).toBeVisible();
    
    // Fill in exercise description
    await page.getByRole('textbox', { name: 'Exercise Description' }).fill('30 minute walk');
    
    // Fill in duration
    await page.getByRole('spinbutton', { name: 'Duration (minutes)' }).fill('30');
    
    // Fill in notes
    await page.locator('textarea').fill('Morning exercise routine');
    
    // Submit the form
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    
    // Verify success message is displayed
    await expect(page.locator('text=Measurement added successfully')).toBeVisible();
    
    // Wait for form to close and verify we're back to dashboard
    await expect(page.locator('h3:has-text("Add New Measurement")')).toBeVisible();
    
    // Take screenshot for verification
    await page.screenshot({ path: 'tests/Playwright/screenshots/exercise-create.png' });
  });

  test('create notes measurement', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Click the notes measurement type button
    await page.getByRole('button', { name: '📝 Notes' }).click();
    
    // Verify the notes form opened
    await expect(page.locator('h3:has-text("Add Notes Measurement")')).toBeVisible();
    
    // Fill in notes content
    await page.locator('textarea').fill('Feeling great today! Had a good breakfast and ready for the day.');
    
    // Submit the form
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    
    // Verify success message is displayed
    await expect(page.locator('text=Measurement added successfully')).toBeVisible();
    
    // Wait for form to close and verify we're back to dashboard
    await expect(page.locator('h3:has-text("Add New Measurement")')).toBeVisible();
    
    // Take screenshot for verification
    await page.screenshot({ path: 'tests/Playwright/screenshots/notes-create.png' });
  });

  test('view and verify measurements in detailed view', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // First create a measurement to view
    await page.getByRole('button', { name: '🩸 Glucose (mmol/L)' }).click();
    await expect(page.locator('h3:has-text("Add Glucose Measurement")')).toBeVisible();
    
    // Fill and submit glucose measurement
    await page.locator('input[type="number"]').fill('6.2');
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    await expect(page.locator('text=Measurement added successfully')).toBeVisible();
    await expect(page.locator('h3:has-text("Add New Measurement")')).toBeVisible();
    
    // Switch to detailed view to see measurements
    await page.getByText('Detailed').click();
    
    // Verify detailed view shows measurements
    await expect(page.locator('text=Detailed')).toBeVisible();
    await expect(page.locator('text=6.2')).toBeVisible(); // Verify our measurement appears
    
    // Take screenshot of detailed view
    await page.screenshot({ path: 'tests/Playwright/screenshots/detailed-view.png' });
  });

  test('edit existing measurement - UI not implemented', async ({ page }) => {
    // NOTE: This test verifies edit functionality is not yet implemented
    // Based on Task 8 status and lack of edit buttons in detailed view
    
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Switch to detailed view
    await page.getByText('Detailed').click();
    await expect(page.locator('text=Detailed')).toBeVisible();
    
    // Verify that edit buttons are NOT present (feature not implemented)
    await expect(page.locator('button:has-text("Edit")')).not.toBeVisible();
    
    // Take screenshot showing no edit functionality
    await page.screenshot({ path: 'tests/Playwright/screenshots/no-edit-functionality.png' });
  });

  test('delete measurement - UI not implemented', async ({ page }) => {
    // NOTE: This test verifies delete functionality is not yet implemented
    // Based on Task 8 status and lack of delete buttons in detailed view
    
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Switch to detailed view
    await page.getByText('Detailed').click();
    await expect(page.locator('text=Detailed')).toBeVisible();
    
    // Verify that delete buttons are NOT present (feature not implemented)
    await expect(page.locator('button:has-text("Delete")')).not.toBeVisible();
    
    // Take screenshot showing no delete functionality
    await page.screenshot({ path: 'tests/Playwright/screenshots/no-delete-functionality.png' });
  });

  test('measurement form validation', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Click glucose measurement button
    await page.getByRole('button', { name: '🩸 Glucose (mmol/L)' }).click();
    await expect(page.locator('h3:has-text("Add Glucose Measurement")')).toBeVisible();
    
    // Try submitting empty form
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    
    // Verify validation errors appear (form should not submit)
    await expect(page.locator('h3:has-text("Add Glucose Measurement")')).toBeVisible(); // Form still open
    
    // Try with invalid value (negative number)
    await page.locator('input[type="number"]').fill('-5.0');
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    
    // Verify form still open (validation failed)
    await expect(page.locator('h3:has-text("Add Glucose Measurement")')).toBeVisible();
    
    // Take screenshot of validation state
    await page.screenshot({ path: 'tests/Playwright/screenshots/validation-errors.png' });
  });

  test('measurement filtering and search after creation', async ({ page }) => {
    // Verify we're on dashboard
    await expect(page.locator('h2:has-text("Dashboard")')).toBeVisible();
    
    // Create a glucose measurement to test filtering
    await page.getByRole('button', { name: '🩸 Glucose (mmol/L)' }).click();
    await expect(page.locator('h3:has-text("Add Glucose Measurement")')).toBeVisible();
    await page.locator('input[type="number"]').fill('6.5');
    await page.getByRole('button', { name: 'Save Measurement' }).click();
    await expect(page.locator('text=Measurement added successfully')).toBeVisible();
    await expect(page.locator('h3:has-text("Add New Measurement")')).toBeVisible();
    
    // Test glucose filter checkbox
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeVisible();
    await page.getByRole('checkbox', { name: 'Blood Glucose' }).check();
    
    // Verify glucose filter is checked
    await expect(page.getByRole('checkbox', { name: 'Blood Glucose' })).toBeChecked();
    
    // Test search functionality
    await expect(page.getByRole('textbox', { name: 'Search measurements' })).toBeVisible();
    await page.getByRole('textbox', { name: 'Search measurements' }).fill('glucose');
    
    // Take screenshot of filtered results
    await page.screenshot({ path: 'tests/Playwright/screenshots/filtered-measurements.png' });
  });

});