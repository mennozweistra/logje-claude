# Playwright E2E Tests for Health Tracking App

This directory contains end-to-end tests using Playwright that verify the complete functionality of the application in real browsers.

## Setup

### 1. Install Dependencies
```bash
npm install
```

### 2. Install Playwright Browsers
```bash
npm run playwright:install
```

### 3. Ensure Application is Running
```bash
docker compose up -d
```

## Running Tests

### Run All Tests
```bash
npm run test:e2e
```

### Run Tests in UI Mode (Interactive)
```bash
npm run test:e2e:ui
```

### Run Tests in Headed Mode (See Browser)
```bash
npm run test:e2e:headed
```

### Debug Tests
```bash
npm run test:e2e:debug
```

### View Test Report
```bash
npm run test:e2e:report
```

### Run Both Laravel and Playwright Tests
```bash
npm run test:all
```

## Test Files

### 1. `DashboardFilterTest.js`
- Tests dashboard filter functionality (type filters, search, date range)
- Verifies HTML comment artifacts are fixed
- Tests view toggle (summary/detailed)
- Tests responsive design
- Tests navigation between pages

### 2. `AuthenticationTest.js`
- Tests user registration flow
- Tests login/logout functionality
- Tests invalid credential handling
- Tests protected route access

## Screenshots

All tests automatically capture screenshots on failure. Successful test screenshots are saved to:
- `tests/Playwright/screenshots/`

## Configuration

The Playwright configuration is in `/playwright.config.js` and includes:
- Cross-browser testing (Chrome, Firefox, Safari)
- Mobile device testing
- Automatic server startup
- Screenshot and video capture on failure
- HTML reports

## Advantages of This Setup

1. **Persistent Tests**: Unlike MCP tools, these tests are saved and can be run repeatedly
2. **Regression Testing**: Perfect for CI/CD pipelines  
3. **Cross-Browser**: Tests work across Chrome, Firefox, Safari
4. **Mobile Testing**: Includes mobile viewport testing
5. **Visual Debugging**: Screenshots and videos on failure
6. **Parallel Execution**: Tests run in parallel for speed
7. **HTML Reports**: Beautiful test reports with screenshots

## Best Practices

1. **Data Isolation**: Each test creates its own test data
2. **Screenshots**: Key interactions are captured for debugging
3. **Waits**: Proper waits for Livewire updates and page loads
4. **Selectors**: Uses semantic selectors (wire:model, etc.)
5. **Error Handling**: Tests both success and failure scenarios

## Integration with Laravel Tests

- Laravel tests verify backend logic and Livewire components
- Playwright tests verify complete user workflows in real browsers
- Both test suites complement each other for comprehensive coverage

## CI/CD Integration

Add to your CI pipeline:
```yaml
- name: Run E2E tests
  run: |
    docker compose up -d
    npm ci
    npm run playwright:install
    npm run test:e2e
```

## Troubleshooting

### Tests Fail to Connect
- Ensure Docker containers are running: `docker compose up -d`
- Check application is accessible: `curl http://localhost:8000`

### Browser Installation Issues
- Run: `npm run playwright:install`
- On CI: Use Playwright Docker image

### Flaky Tests
- Increase timeouts in playwright.config.js
- Add more specific waits for Livewire updates
- Check for race conditions in test setup