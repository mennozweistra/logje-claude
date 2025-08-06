# Available Tools and Commands

This document lists project-specific tools and their command-line instructions. When AI resolves tool call issues, the solutions are documented here to prevent repeated struggles.

## Development Tools

### Docker Development Environment

**Setup Requirements:**
```bash
# Export user environment variables (add to .bashrc/.zshrc for persistence)
export USER_ID=$(id -u)
export GROUP_ID=$(id -g) 
export USER_NAME=$(whoami)
```

**Primary Development Setup:**
```bash
# Start all services (app, database, phpmyadmin)
docker compose up -d

# Stop all services
docker compose down

# Rebuild containers (after Dockerfile changes)
docker compose build --no-cache

# View running containers
docker compose ps

# View application logs
docker compose logs app
docker compose logs app -f  # Follow logs in real-time

# Access application shell
docker compose exec app bash

# Run Laravel commands
docker compose exec app php artisan migrate
docker compose exec app php artisan make:model MyModel
docker compose exec app php artisan config:clear
docker compose exec app php artisan about

# Run Composer commands
docker compose exec app composer install
docker compose exec app composer update
docker compose exec app composer require package-name

# Run NPM commands
docker compose exec app npm install
docker compose exec app npm run dev
docker compose exec app npm run build

# Access database
docker compose exec mysql mysql -u root -p logje
# Use password: password
```

**Service URLs:**
- Application: http://localhost:8000
- phpMyAdmin: http://localhost:8081
- MySQL: localhost:3307

**Development Workflow:**
- Proper user ID mapping ensures seamless file editing between host and container
- Laravel app runs in container with live code mounting at /var/www/html
- Database data persists in Docker volumes
- All files created have correct ownership (host user UID 1000)
- No permission fixing required with proper Docker setup

**Important Configuration Notes:**
- `.env` file must use `DB_HOST=mysql` (Docker service name) not `127.0.0.1`
- `.env` file must use `DB_PORT=3306` (internal container port) not `3307`
- Port 3307 is only for external host connections to the database
- Laravel container connects to MySQL container via Docker network using service name

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

## File Editing Best Practices

### Edit Tool Usage Instructions

**CRITICAL**: When using the Edit tool (MultiEdit, etc.), always provide sufficient unique context to avoid ambiguity errors.

#### Rule 1: Unique Context Required
Always include enough surrounding text to uniquely identify the target occurrence when the string appears multiple times in a file.

**Correct Usage (unique context):**
```
Edit(.claude/tasks.md):
  old_string: |
    6. [ ] Run full test suite to ensure no regressions
    - **Started**: 
    - **Review**: 
    - **Completed**: 
  new_string: |
    6. [x] Run full test suite to ensure no regressions  
    - **Started**: 2025-08-04 14:00
    - **Review**: 2025-08-04 15:00
    - **Completed**: 2025-08-04 15:20
```

#### Rule 2: Use replace_all for Global Changes
Set `replace_all: true` when intentionally replacing every occurrence of a string.

**Correct Usage (replace all):**
```
Edit(.claude/tasks.md):
  old_string: "- **Duration**:"
  new_string: "- **Duration (estimated)**:"
  replace_all: true
```

#### Rule 3: Avoid Ambiguous Strings
Never use short, generic strings that appear multiple times without sufficient context.

**Incorrect (will cause ambiguity errors):**
```
Edit(.claude/tasks.md):
  old_string: "- **Duration**: "
  new_string: "- **Duration**: 30 minutes"
```

**Error Message Indicates**: "Found X matches of the string to replace, but replace_all is false"

#### Solution Strategies:
1. **Add More Context**: Include 2-3 lines before/after the target
2. **Use replace_all**: If you want to change all occurrences  
3. **Be More Specific**: Use unique identifiers like task numbers, function names, etc.

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