/**
 * Test Helper Functions
 * 
 * Common utilities for error handling, retries, and test reliability
 */

/**
 * Wait for Livewire to finish loading/updating
 * @param {Page} page - Playwright page object
 * @param {number} timeout - Maximum wait time in ms
 */
export async function waitForLivewire(page, timeout = 5000) {
  try {
    await page.waitForFunction(() => {
      // Wait for any Livewire loading indicators to disappear
      const loadingElements = document.querySelectorAll('[wire\\:loading]');
      return Array.from(loadingElements).every(el => !el.hasAttribute('wire:loading') || el.style.display === 'none');
    }, { timeout });
  } catch (error) {
    console.warn('Livewire loading wait timed out, continuing...');
  }
}

/**
 * Retry an action with exponential backoff
 * @param {Function} action - Function to retry
 * @param {number} maxRetries - Maximum number of retries
 * @param {number} baseDelay - Base delay in ms
 */
export async function retryAction(action, maxRetries = 3, baseDelay = 1000) {
  let lastError;
  
  for (let attempt = 0; attempt <= maxRetries; attempt++) {
    try {
      return await action();
    } catch (error) {
      lastError = error;
      
      if (attempt === maxRetries) {
        throw error;
      }
      
      // Exponential backoff: 1s, 2s, 4s
      const delay = baseDelay * Math.pow(2, attempt);
      console.log(`Attempt ${attempt + 1} failed, retrying in ${delay}ms...`);
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
  
  throw lastError;
}

/**
 * Safe click with retry logic
 * @param {Page} page - Playwright page object
 * @param {string} selector - Element selector
 * @param {Object} options - Click options
 */
export async function safeClick(page, selector, options = {}) {
  return retryAction(async () => {
    const element = page.locator(selector);
    await element.waitFor({ state: 'visible', timeout: 5000 });
    await element.click(options);
  });
}

/**
 * Safe fill with retry logic
 * @param {Page} page - Playwright page object
 * @param {string} selector - Element selector
 * @param {string} value - Value to fill
 */
export async function safeFill(page, selector, value) {
  return retryAction(async () => {
    const element = page.locator(selector);
    await element.waitFor({ state: 'visible', timeout: 5000 });
    await element.clear();
    await element.fill(value);
  });
}

/**
 * Safe navigation with retry
 * @param {Page} page - Playwright page object
 * @param {string} url - URL to navigate to
 */
export async function safeNavigate(page, url) {
  return retryAction(async () => {
    await page.goto(url);
    await page.waitForLoadState('networkidle', { timeout: 10000 });
  });
}

/**
 * Wait for element with retry
 * @param {Page} page - Playwright page object
 * @param {string} selector - Element selector
 * @param {Object} options - Wait options
 */
export async function waitForElement(page, selector, options = {}) {
  const defaultOptions = { state: 'visible', timeout: 10000, ...options };
  
  return retryAction(async () => {
    const element = page.locator(selector);
    await element.waitFor(defaultOptions);
    return element;
  });
}

/**
 * Create unique test user data
 * @param {string} prefix - Prefix for user data
 */
export function createTestUser(prefix = 'Test') {
  const timestamp = Date.now();
  return {
    name: `${prefix} User ${timestamp}`,
    email: `${prefix.toLowerCase()}${timestamp}@example.com`,
    password: 'password123'
  };
}

/**
 * Register and login user helper
 * @param {Page} page - Playwright page object
 * @param {string} baseUrl - Base URL
 * @param {Object} testUser - User data
 */
export async function registerAndLogin(page, baseUrl, testUser = null) {
  if (!testUser) {
    testUser = createTestUser();
  }
  
  // Register user
  await safeNavigate(page, `${baseUrl}/register`);
  await safeFill(page, 'input[name="name"]', testUser.name);
  await safeFill(page, 'input[name="email"]', testUser.email);
  await safeFill(page, 'input[name="password"]', testUser.password);
  await safeFill(page, 'input[name="password_confirmation"]', testUser.password);
  await safeClick(page, 'button[type="submit"]');
  
  // Wait for redirect to dashboard
  await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 10000 });
  
  return testUser;
}

/**
 * Logout user helper
 * @param {Page} page - Playwright page object
 * @param {string} userName - User name to find in dropdown
 */
export async function logout(page, userName) {
  return retryAction(async () => {
    // Click on user menu button to reveal dropdown
    await safeClick(page, `button:has-text("${userName}")`);
    
    // Wait for dropdown and click logout
    await waitForElement(page, 'button:has-text("Log Out")');
    await safeClick(page, 'button:has-text("Log Out")');
  });
}

/**
 * Handle potential modal or confirmation dialogs
 * @param {Page} page - Playwright page object
 * @param {boolean} accept - Whether to accept or reject
 */
export async function handleDialog(page, accept = true) {
  try {
    // Wait a bit for any dialog to appear
    await page.waitForTimeout(500);
    
    const confirmButton = page.locator('button:has-text("Confirm"), button:has-text("Yes"), button:has-text("OK")');
    const cancelButton = page.locator('button:has-text("Cancel"), button:has-text("No")');
    
    if (accept && await confirmButton.isVisible()) {
      await confirmButton.click();
    } else if (!accept && await cancelButton.isVisible()) {
      await cancelButton.click();
    }
  } catch (error) {
    // No dialog present, continue
  }
}

/**
 * Take screenshot with error context
 * @param {Page} page - Playwright page object
 * @param {string} name - Screenshot name
 * @param {Error} error - Error that occurred
 */
export async function takeErrorScreenshot(page, name, error = null) {
  try {
    const timestamp = Date.now();
    const fileName = `tests/Playwright/screenshots/error-${name}-${timestamp}.png`;
    await page.screenshot({ path: fileName, fullPage: true });
    
    if (error) {
      console.error(`Error occurred in ${name}: ${error.message}`);
      console.log(`Screenshot saved: ${fileName}`);
    }
    
    return fileName;
  } catch (screenshotError) {
    console.warn('Failed to take error screenshot:', screenshotError.message);
  }
}

/**
 * Assert element exists with retry
 * @param {Page} page - Playwright page object
 * @param {string} selector - Element selector
 * @param {string} message - Custom error message
 */
export async function assertElementExists(page, selector, message = '') {
  return retryAction(async () => {
    const element = page.locator(selector);
    const isVisible = await element.isVisible();
    
    if (!isVisible) {
      await takeErrorScreenshot(page, 'element-not-found');
      throw new Error(`Element not found: ${selector}. ${message}`);
    }
    
    return element;
  });
}