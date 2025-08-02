# Available Tools and Commands

This document lists project-specific tools and their command-line instructions. When AI resolves tool call issues, the solutions are documented here to prevent repeated struggles.

## Development Tools

<!-- Development environment tools and commands -->

## Testing Tools

### Playwright E2E Testing

**Two Testing Approaches Available:**

#### 1. MCP Playwright Tools (Interactive Development/Debugging)
- **Purpose**: Live testing, debugging, exploration during development
- **Usage**: Available through MCP tools in Claude Code sessions
- **Commands**: 
  - `mcp__playwright__browser_navigate` - Navigate to URLs
  - `mcp__playwright__browser_click` - Click elements
  - `mcp__playwright__browser_type` - Type text into fields
  - `mcp__playwright__browser_snapshot` - Get page accessibility snapshot
  - `mcp__playwright__browser_take_screenshot` - Capture screenshots
- **Browser Control**: Uses managed browsers, does not open system default browser
- **Best For**: Manual testing, debugging UI issues, exploring functionality

#### 2. Command-line Playwright (Persistent Test Suites)
- **Purpose**: Automated regression testing, CI/CD integration
- **Test Location**: `tests/Playwright/`
- **Configuration**: `playwright.config.js`

**Available Browser Projects:**
```bash
# Headless testing (default, for CI/CD)
npx playwright test --project=chromium        # Playwright's managed Chromium
npx playwright test --project=firefox         # Playwright's managed Firefox
npx playwright test --project=webkit          # Playwright's managed WebKit

# UI testing (for debugging with visible browser)
npx playwright test --project=chromium-ui     # Opens Playwright's Chromium browser
npx playwright test --project=firefox-ui      # Opens Playwright's Firefox browser

# System browser testing (uses installed browsers)
npx playwright test --project=chrome-system   # Uses system Google Chrome
npx playwright test --project=chromium-system # Uses system Chromium

# Mobile testing
npx playwright test --project="Mobile Chrome" # Mobile Chrome viewport
npx playwright test --project="Mobile Safari" # Mobile Safari viewport
```

**Common Commands:**
```bash
# List all available tests
npx playwright test --list

# Run specific test file
npx playwright test dashboard-filter.spec.js

# Run specific test by name pattern
npx playwright test -g "dashboard loads and displays correctly"

# Run with specific timeout
npx playwright test --timeout=30000

# Generate and view HTML report (manual - won't auto-open)
npx playwright show-report tests/Playwright/reports

# Generate report and open in specific browser (avoid default browser interruption)
npx playwright show-report tests/Playwright/reports &
# Then manually open http://localhost:9323 in your preferred browser

# Run tests and keep browser open for debugging
npx playwright test --project=chromium-ui --debug

# View test results without browser interruption
npx playwright test --reporter=list  # Console output only
```

**Browser Control Solution:**
- **Problem**: Tests were opening in system default browser (LibreWolf)
- **Root Cause**: Using `devices['Desktop Chrome']` caused Playwright to seek system Chrome, falling back to default browser
- **Solution**: 
  1. Removed global `headless: true` setting and properly configured browser projects
  2. **CRITICAL**: Removed `devices['Desktop Chrome/Firefox/Safari']` configurations
  3. Used explicit viewport and headless settings with no channel specification
  4. No channel = forces Playwright's managed browsers instead of system browsers
- **Result**: Playwright now reliably uses its managed browsers, with separate UI projects for debugging

## Build Tools

<!-- Build, compile, and deployment commands -->

## Debugging Tools

<!-- Debugging and troubleshooting tools -->

## Resolved Tool Issues

### Playwright Browser Control Issue
```
**Issue**: Playwright tests were opening in system default browser (LibreWolf) instead of controlled browsers
**Root Cause**: Using devices['Desktop Chrome'] in project configurations caused Playwright to search for system browsers
**Solution**: 
1. Removed global `headless: true` setting from playwright.config.js shared use section
2. **CRITICAL FIX**: Removed `...devices['Desktop Chrome/Firefox/Safari']` from all project configurations
3. Created separate UI projects (chromium-ui, firefox-ui) with `headless: false` for debugging
4. Used explicit viewport and headless settings with NO channel specification
5. Added system browser projects with proper `channel` configurations for when needed
6. No channel = forces Playwright's managed browsers instead of system browser fallbacks
**Date**: 2025-08-02
**Result**: Both MCP and CLI Playwright now use proper browser control reliably
**Key Learning**: Device configurations can cause unexpected browser fallback behavior
```

### Playwright Error Report Auto-Opening Issue
```
**Issue**: Playwright HTML error reports auto-opened in default browser (LibreWolf), interrupting workflow
**Root Cause**: Default HTML reporter configuration opens browser automatically on test failures
**Solution**: Added `open: 'never'` to HTML reporter configuration in playwright.config.js:
  reporter: [
    ['html', { 
      outputFolder: 'tests/Playwright/reports',
      open: 'never' // Don't auto-open reports in browser
    }],
    // ... other reporters
  ]
**Manual Access**: 
  - View reports when needed: `npx playwright show-report tests/Playwright/reports`
  - Console shows: "To open last HTML report run: npx playwright show-report tests/Playwright/reports"
  - Alternative: Use `--reporter=list` for console-only output
**Date**: 2025-08-02
**Result**: Error reports no longer interrupt default browser workflow, but remain accessible on demand
**Key Learning**: Playwright reporters can be configured to prevent auto-opening while maintaining accessibility
```

---

*Tool documentation is maintained as part of the workflow defined in `./.claude/workflow.md`*

*For global Claude Code setup (MCP servers, permissions), see your dotfiles repo.*