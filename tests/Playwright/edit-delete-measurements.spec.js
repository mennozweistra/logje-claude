import { test, expect } from '@playwright/test';

test.describe('Edit and Delete Measurements (Task 8)', () => {
  let page;

  test.beforeEach(async ({ browser }) => {
    page = await browser.newPage();
    
    // Login as test user
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    
    // Wait for dashboard to load
    await page.waitForURL('**/dashboard');
    await expect(page.locator('h1')).toContainText('Saturday, August 2, 2025');
  });

  test.afterEach(async () => {
    await page.close();
  });

  test.describe('Edit Functionality', () => {
    test('can edit a glucose measurement', async () => {
      // Switch to detailed view to see edit buttons
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Wait for detailed view to load
      await expect(page.locator('.space-y-4')).toBeVisible();
      
      // Find the first glucose measurement and click edit
      const firstGlucoseMeasurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      await firstGlucoseMeasurement.locator('button[title="Edit measurement"]').click();
      
      // Verify edit modal opens
      await expect(page.locator('h3:text("Edit Glucose Measurement")')).toBeVisible();
      
      // Verify form is pre-populated
      const glucoseValueInput = page.locator('input[type="number"][step="0.1"]');
      const timeInput = page.locator('input[type="time"]');
      const notesTextarea = page.locator('textarea');
      
      await expect(glucoseValueInput).toHaveValue(/\d+(\.\d+)?/);
      await expect(timeInput).toHaveValue(/\d{2}:\d{2}/);
      
      // Edit the values
      await glucoseValueInput.fill('7.2');
      await timeInput.fill('10:30');
      await notesTextarea.fill('Updated glucose reading - feeling good');
      
      // Save the changes
      await page.click('button:text("Update Measurement")');
      
      // Verify modal closes
      await expect(page.locator('h3:text("Edit Glucose Measurement")')).not.toBeVisible();
      
      // Verify the updated values appear in the detailed view
      await expect(page.locator('.space-y-4')).toContainText('7.20 mmol/L');
      await expect(page.locator('.space-y-4')).toContainText('10:30');
      await expect(page.locator('.space-y-4')).toContainText('Updated glucose reading - feeling good');
    });

    test('can edit a weight measurement', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Find a weight measurement and click edit
      const weightMeasurement = page.locator('div:has(.text-gray-900:text("Weight"))').first();
      await weightMeasurement.locator('button[title="Edit measurement"]').click();
      
      // Verify edit modal opens
      await expect(page.locator('h3:text("Edit Weight Measurement")')).toBeVisible();
      
      // Edit the values
      await page.fill('input[id="weightValue"]', '77.8');
      await page.fill('input[id="weightTime"]', '07:45');
      await page.fill('textarea[id="weightNotes"]', 'Updated weight measurement');
      
      // Save the changes
      await page.click('button:text("Update Measurement")');
      
      // Verify modal closes and values are updated
      await expect(page.locator('h3:text("Edit Weight Measurement")')).not.toBeVisible();
      await expect(page.locator('.space-y-4')).toContainText('77.80 kg');
      await expect(page.locator('.space-y-4')).toContainText('07:45');
      await expect(page.locator('.space-y-4')).toContainText('Updated weight measurement');
    });

    test('can edit an exercise measurement', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Find an exercise measurement and click edit
      const exerciseMeasurement = page.locator('div:has(.text-gray-900:text("Exercise"))').first();
      await exerciseMeasurement.locator('button[title="Edit measurement"]').click();
      
      // Verify edit modal opens
      await expect(page.locator('h3:text("Edit Exercise Measurement")')).toBeVisible();
      
      // Edit the values
      await page.fill('input[id="exerciseDescription"]', 'Updated cycling workout');
      await page.fill('input[id="exerciseDuration"]', '60');
      await page.fill('input[id="exerciseTime"]', '18:30');
      await page.fill('textarea[id="exerciseNotes"]', 'Great evening workout');
      
      // Save the changes
      await page.click('button:text("Update Measurement")');
      
      // Verify modal closes and values are updated
      await expect(page.locator('h3:text("Edit Exercise Measurement")')).not.toBeVisible();
      await expect(page.locator('.space-y-4')).toContainText('Updated cycling workout');
      await expect(page.locator('.space-y-4')).toContainText('60 minutes');
      await expect(page.locator('.space-y-4')).toContainText('18:30');
      await expect(page.locator('.space-y-4')).toContainText('Great evening workout');
    });

    test('can edit a notes measurement', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Find a notes measurement and click edit
      const notesMeasurement = page.locator('div:has(.text-gray-900:text("Notes"))').first();
      await notesMeasurement.locator('button[title="Edit measurement"]').click();
      
      // Verify edit modal opens
      await expect(page.locator('h3:text("Edit Notes Measurement")')).toBeVisible();
      
      // Edit the values
      await page.fill('input[id="notesTime"]', '13:15');
      await page.fill('textarea[id="notesContent"]', 'Updated daily notes - had a productive morning!');
      
      // Save the changes
      await page.click('button:text("Update Measurement")');
      
      // Verify modal closes and values are updated
      await expect(page.locator('h3:text("Edit Notes Measurement")')).not.toBeVisible();
      await expect(page.locator('.space-y-4')).toContainText('13:15');
      await expect(page.locator('.space-y-4')).toContainText('Updated daily notes - had a productive morning!');
    });

    test('validates glucose value range during edit', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Find a glucose measurement and click edit
      const glucoseMeasurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      await glucoseMeasurement.locator('button[title="Edit measurement"]').click();
      
      // Try to enter invalid value
      await page.fill('input[id="glucoseValue"]', '100');
      await page.click('button:text("Update Measurement")');
      
      // Verify validation error appears
      await expect(page.locator('text="The glucose value field must not be greater than 50."')).toBeVisible();
      
      // Modal should still be open
      await expect(page.locator('h3:text("Edit Glucose Measurement")')).toBeVisible();
    });

    test('can cancel editing', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Find a measurement and click edit
      const measurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      await measurement.locator('button[title="Edit measurement"]').click();
      
      // Verify modal opens
      await expect(page.locator('h3:text("Edit Glucose Measurement")')).toBeVisible();
      
      // Click cancel
      await page.click('button:text("Cancel")');
      
      // Verify modal closes
      await expect(page.locator('h3:text("Edit Glucose Measurement")')).not.toBeVisible();
    });
  });

  test.describe('Delete Functionality', () => {
    test('can delete a measurement', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Count initial measurements
      const initialCount = await page.locator('div:has(.text-gray-900:text("Glucose"))').count();
      
      // Find a glucose measurement and click delete
      const firstGlucoseMeasurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      const measurementText = await firstGlucoseMeasurement.textContent();
      await firstGlucoseMeasurement.locator('button[title="Delete measurement"]').click();
      
      // Verify delete confirmation modal opens
      await expect(page.locator('h3:text("Delete Measurement")')).toBeVisible();
      await expect(page.locator('text="Are you sure you want to delete this Glucose measurement?"')).toBeVisible();
      await expect(page.locator('text="This action cannot be undone."')).toBeVisible();
      
      // Confirm deletion
      await page.click('button:text("Delete")');
      
      // Verify modal closes
      await expect(page.locator('h3:text("Delete Measurement")')).not.toBeVisible();
      
      // Verify measurement is removed from the list
      const finalCount = await page.locator('div:has(.text-gray-900:text("Glucose"))').count();
      expect(finalCount).toBe(initialCount - 1);
      
      // Verify the specific measurement is no longer present
      await expect(page.locator('.space-y-4')).not.toContainText(measurementText);
    });

    test('can cancel deletion', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Count initial measurements
      const initialCount = await page.locator('div:has(.text-gray-900:text("Glucose"))').count();
      
      // Find a measurement and click delete
      const measurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      await measurement.locator('button[title="Delete measurement"]').click();
      
      // Verify delete confirmation modal opens
      await expect(page.locator('h3:text("Delete Measurement")')).toBeVisible();
      
      // Cancel deletion
      await page.click('button:text("Cancel")');
      
      // Verify modal closes
      await expect(page.locator('h3:text("Delete Measurement")')).not.toBeVisible();
      
      // Verify measurement count hasn't changed
      const finalCount = await page.locator('div:has(.text-gray-900:text("Glucose"))').count();
      expect(finalCount).toBe(initialCount);
    });

    test('delete confirmation shows correct measurement type', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Test different measurement types
      const measurementTypes = [
        { type: 'Glucose', expectedText: 'Glucose measurement' },
        { type: 'Weight', expectedText: 'Weight measurement' },
        { type: 'Exercise', expectedText: 'Exercise measurement' },
        { type: 'Notes', expectedText: 'Notes measurement' }
      ];
      
      for (const { type, expectedText } of measurementTypes) {
        const measurements = page.locator(`div:has(.text-gray-900:text("${type}"))`);
        const count = await measurements.count();
        
        if (count > 0) {
          await measurements.first().locator('button[title="Delete measurement"]').click();
          await expect(page.locator(`text="Are you sure you want to delete this ${expectedText}?"`)).toBeVisible();
          await page.click('button:text("Cancel")');
        }
      }
    });
  });

  test.describe('Integration Tests', () => {
    test('edit and delete buttons only appear in detailed view', async () => {
      // Verify buttons are not visible in summary view
      await expect(page.locator('button[title="Edit measurement"]')).not.toBeVisible();
      await expect(page.locator('button[title="Delete measurement"]')).not.toBeVisible();
      
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Verify buttons are now visible
      await expect(page.locator('button[title="Edit measurement"]').first()).toBeVisible();
      await expect(page.locator('button[title="Delete measurement"]').first()).toBeVisible();
      
      // Switch back to summary view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Verify buttons are hidden again
      await expect(page.locator('button[title="Edit measurement"]')).not.toBeVisible();
      await expect(page.locator('button[title="Delete measurement"]')).not.toBeVisible();
    });

    test('detailed view toggle works without JavaScript errors', async () => {
      // Check console for errors before toggling
      const errorLogs = [];
      page.on('console', msg => {
        if (msg.type() === 'error') {
          errorLogs.push(msg.text());
        }
      });
      
      // Toggle to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      await expect(page.locator('.space-y-4')).toBeVisible();
      
      // Toggle back to summary view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      await expect(page.locator('.grid.grid-cols-1')).toBeVisible();
      
      // Verify no JavaScript errors occurred
      expect(errorLogs).toHaveLength(0);
    });

    test('edit form handles different measurement types correctly', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Test glucose measurement edit form
      const glucoseMeasurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      if (await glucoseMeasurement.count() > 0) {
        await glucoseMeasurement.locator('button[title="Edit measurement"]').click();
        await expect(page.locator('input[id="glucoseValue"]')).toBeVisible();
        await expect(page.locator('input[type="checkbox"][id="isFasting"]')).toBeVisible();
        await page.click('button:text("Cancel")');
      }
      
      // Test weight measurement edit form
      const weightMeasurement = page.locator('div:has(.text-gray-900:text("Weight"))').first();
      if (await weightMeasurement.count() > 0) {
        await weightMeasurement.locator('button[title="Edit measurement"]').click();
        await expect(page.locator('input[id="weightValue"]')).toBeVisible();
        await expect(page.locator('textarea[id="weightNotes"]')).toBeVisible();
        await page.click('button:text("Cancel")');
      }
      
      // Test exercise measurement edit form
      const exerciseMeasurement = page.locator('div:has(.text-gray-900:text("Exercise"))').first();
      if (await exerciseMeasurement.count() > 0) {
        await exerciseMeasurement.locator('button[title="Edit measurement"]').click();
        await expect(page.locator('input[id="exerciseDescription"]')).toBeVisible();
        await expect(page.locator('input[id="exerciseDuration"]')).toBeVisible();
        await page.click('button:text("Cancel")');
      }
      
      // Test notes measurement edit form
      const notesMeasurement = page.locator('div:has(.text-gray-900:text("Notes"))').first();
      if (await notesMeasurement.count() > 0) {
        await notesMeasurement.locator('button[title="Edit measurement"]').click();
        await expect(page.locator('textarea[id="notesContent"]')).toBeVisible();
        await page.click('button:text("Cancel")');
      }
    });

    test('page refreshes data after edit/delete operations', async () => {
      // Switch to detailed view
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      
      // Find a measurement to edit
      const measurement = page.locator('div:has(.text-gray-900:text("Glucose"))').first();
      await measurement.locator('button[title="Edit measurement"]').click();
      
      // Make a distinctive change
      const uniqueNote = `Test note ${Date.now()}`;
      await page.fill('textarea', uniqueNote);
      await page.click('button:text("Update Measurement")');
      
      // Verify the change appears immediately (data refresh)
      await expect(page.locator('.space-y-4')).toContainText(uniqueNote);
      
      // Refresh the page and verify persistence
      await page.reload();
      await page.click('button[class*="relative inline-flex h-6 w-11"]');
      await expect(page.locator('.space-y-4')).toContainText(uniqueNote);
    });
  });
});