/**
 * Playwright Configuration for Laravel Health Tracking App
 * 
 * See https://playwright.dev/docs/test-configuration
 */

import { defineConfig, devices } from '@playwright/test';

/**
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  testDir: './tests/Playwright',
  
  /* Run tests in files in parallel */
  fullyParallel: true,
  
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  
  /* Retry failed tests */
  retries: process.env.CI ? 2 : 1, // Retry once locally, twice on CI
  
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: [
    ['html', { 
      outputFolder: 'tests/Playwright/reports',
      open: 'never' // Don't auto-open reports in browser - prevents LibreWolf interruption
    }],
    ['json', { outputFile: 'tests/Playwright/results.json' }],
    ['list']
  ],
  
  /* Global test timeout */
  timeout: 30 * 1000, // 30 seconds per test
  
  /* Expect timeout for assertions */
  expect: {
    timeout: 10 * 1000, // 10 seconds for assertions
  },

  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.APP_URL || 'http://localhost:8000',
    
    /* Slow down actions for video recording (200ms delay between actions) */
    launchOptions: {
      slowMo: process.env.SLOWMO ? parseInt(process.env.SLOWMO) : 200,
    },
    
    /* Navigation timeout */
    navigationTimeout: 15 * 1000, // 15 seconds for navigation
    
    /* Action timeout */
    actionTimeout: 10 * 1000, // 10 seconds for actions

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
    
    /* Screenshot on failure */
    screenshot: 'only-on-failure',
    
    /* Video recording */
    video: 'retain-on-failure',
    
    /* Additional error handling options */
    ignoreHTTPSErrors: true,
    
    /* Retry click actions that might fail due to animations */
    retryClickTimeout: 3000,
  },

  /* Configure projects for major browsers */
  projects: [
    // Primary Playwright-managed browsers (headless by default)
    {
      name: 'chromium',
      use: { 
        // Use Playwright's managed Chromium browser (no channel specified = managed browser)
        viewport: { width: 1280, height: 720 },
        headless: true,
      },
    },

    {
      name: 'firefox',
      use: { 
        // Use Playwright's managed Firefox browser
        viewport: { width: 1280, height: 720 },
        headless: true,
      },
    },

    {
      name: 'webkit',
      use: { 
        // Use Playwright's managed WebKit browser
        viewport: { width: 1280, height: 720 },
        headless: true,
      },
    },

    // UI testing projects (for debugging with visible browser)
    {
      name: 'chromium-ui',
      use: { 
        viewport: { width: 1720, height: 1440 },
        headless: false, // Show Playwright's managed Chromium for debugging
      },
    },

    {
      name: 'firefox-ui',
      use: { 
        viewport: { width: 1720, height: 1440 },
        headless: false, // Show Playwright's managed Firefox for debugging
      },
    },

    // System browser options (use system-installed browsers)
    {
      name: 'chrome-system',
      use: { 
        ...devices['Desktop Chrome'], 
        channel: 'chrome', // Use system Google Chrome
      },
    },

    {
      name: 'chromium-system',
      use: { 
        ...devices['Desktop Chrome'], 
        channel: 'chromium', // Use system Chromium
      },
    },

    /* Test against mobile viewports using Playwright browsers */
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  /* Run your local dev server before starting the tests */
  webServer: {
    command: 'docker compose up -d',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120 * 1000,
  },
});