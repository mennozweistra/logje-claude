# Logje Development Commands

This file contains all the commands needed for developing the Logje health tracker application.

## Docker Management

### Start Development Environment
```bash
docker-compose up -d                    # Start all containers (app, mysql, phpmyadmin)
```

### Stop Development Environment
```bash
docker-compose down                     # Stop all containers
docker-compose down -v                  # Stop containers and remove volumes (deletes database!)
```

### Check Container Status
```bash
docker-compose ps                       # Show running containers
docker logs logje-app                   # View Laravel app logs
docker logs logje-mysql                 # View MySQL logs
```

### Access Containers
```bash
docker exec -it logje-app bash          # Enter Laravel container
docker exec -it logje-mysql mysql -u root -p  # Access MySQL directly
```

## Application Access

- **Laravel App**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8081
- **MySQL**: localhost:3307 (from host machine)

## Testing Commands

### Unit and Feature Tests
```bash
docker exec logje-app php artisan test                    # Run all tests
docker exec logje-app php artisan test --testsuite=Unit   # Unit tests only
docker exec logje-app php artisan test --testsuite=Feature # Feature tests only
docker exec logje-app php artisan test --filter="TestName" # Specific test
```

### E2E Tests with Playwright

#### Interactive Testing (MCP Tools in Claude Code)
These run in Claude Code conversation, not command line:
```
mcp__playwright__browser_navigate http://localhost:8000
mcp__playwright__browser_take_screenshot
mcp__playwright__browser_click "button"
mcp__playwright__browser_type "input" "text"
```

#### Command-line Testing (Persistent Test Suites)

**Run Tests in Visible Browser (for debugging/watching):**
```bash
# Single test in visible browser
npx playwright test --project=chromium-ui authentication-comprehensive.spec.js -g "user registration flow with validation"

# All tests in visible browser
npx playwright test --project=chromium-ui

# Specific test file in visible browser
npx playwright test --project=chromium-ui dashboard-comprehensive.spec.js

# Interactive UI mode (RECOMMENDED for test review and debugging)
npx playwright test --ui

# Debug mode (step-by-step with browser open)
npx playwright test --project=chromium-ui --debug dashboard-comprehensive.spec.js -g "dashboard structure"

# Run tests with slower timing (for recording with OBS, screen capture, or detailed observation)
SLOWMO=1000 npx playwright test --project=chromium-ui dashboard-filter.spec.js -g "measurement type filters"
SLOWMO=1500 npx playwright test --project=chromium-ui measurement-crud.spec.js -g "create glucose measurement"

# Custom slowdown values (in milliseconds)
SLOWMO=500  npx playwright test --project=chromium-ui   # 500ms delay between actions
SLOWMO=2000 npx playwright test --project=chromium-ui   # 2 second delay between actions
```

**Sequential Test Execution (prevents browser window overlap):**
```bash
# Run tests one at a time using workers flag (recommended for UI review)
npx playwright test --project=chromium-ui --workers=1

# Alternative: Use environment variable
PLAYWRIGHT_WORKERS=1 npx playwright test --project=chromium-ui

# Sequential with slow motion for detailed observation
PLAYWRIGHT_WORKERS=1 SLOWMO=1000 npx playwright test --project=chromium-ui

# Run specific test file sequentially
npx playwright test --project=chromium-ui measurement-crud.spec.js --workers=1

# Sequential execution with specific test pattern
npx playwright test --project=chromium-ui --workers=1 -g "create.*measurement"
```

**Run Tests Headless (for CI/CD):**
```bash
# All tests headless
npx playwright test

# Specific browser
npx playwright test --project=chromium

# Specific test file
npx playwright test authentication-comprehensive.spec.js

# Run with specific timeout
npx playwright test --timeout=30000
```

**Available Browser Projects:**
- `--project=chromium-ui` - Visible Playwright Chromium
- `--project=firefox-ui` - Visible Playwright Firefox  
- `--project=chrome-system` - System Google Chrome
- `--project=chromium-system` - System Chromium browser
- `--project=chromium` - Headless Playwright Chromium (default)
- `--project=firefox` - Headless Playwright Firefox
- `--project=webkit` - Headless Playwright WebKit

**Available Test Files:**
1. `authentication-comprehensive.spec.js` - Registration, login, logout
2. `dashboard-comprehensive.spec.js` - Dashboard filters, navigation  
3. `measurement-crud.spec.js` - Create, edit, delete measurements
4. `robust-authentication.spec.js` - Error handling examples

**View Test Reports:**
```bash
# Generate and view HTML report (won't auto-open browser)
npx playwright show-report tests/Playwright/reports

# View reports in specific browser to avoid interrupting default browser
npx playwright show-report tests/Playwright/reports &
# Then manually open http://localhost:9323 in your preferred browser

# Console-only output (no HTML report)
npx playwright test --reporter=list
```

## Database Management

### Migrations
```bash
docker exec logje-app php artisan migrate           # Run migrations
docker exec logje-app php artisan migrate:rollback  # Rollback last migration
docker exec logje-app php artisan migrate:fresh     # Drop all tables and re-run migrations
```

### Seeders
```bash
docker exec logje-app php artisan db:seed           # Run all seeders
docker exec logje-app php artisan db:seed --class=UserSeeder # Run specific seeder
```

### Database Reset
```bash
docker exec logje-app php artisan migrate:fresh --seed # Fresh database with seed data
```

## Laravel Artisan Commands

### Cache Management
```bash
docker exec logje-app php artisan cache:clear       # Clear application cache
docker exec logje-app php artisan config:clear      # Clear config cache
docker exec logje-app php artisan route:clear       # Clear route cache
docker exec logje-app php artisan view:clear        # Clear view cache
```

### Development Tools
```bash
docker exec logje-app php artisan tinker            # Laravel REPL
docker exec logje-app php artisan route:list        # Show all routes
docker exec logje-app php artisan make:model User   # Create model
docker exec logje-app php artisan make:controller UserController # Create controller
```

## Asset Management

### Frontend Build
```bash
docker exec logje-app npm run dev                   # Development build (watch mode)
docker exec logje-app npm run build                 # Production build
docker exec logje-app npm run build -- --watch     # Development build with watch
```

## Quick Development Workflow

### Daily Startup
```bash
docker-compose up -d                                # Start containers
```

### Daily Shutdown
```bash
docker-compose down                                  # Stop containers
```

### Run All Tests
```bash
docker exec logje-app php artisan test              # Unit/Feature tests
```

## Troubleshooting

### Common Issues
```bash
# If containers won't start
docker-compose down -v && docker-compose up -d

# If database connection fails during tests
docker exec logje-app php artisan migrate --env=testing

# If frontend assets aren't loading
docker exec logje-app npm run build

# If database connection fails during tests
docker exec logje-app php artisan migrate --env=testing
```

### Log Files
- Laravel logs: `docker exec logje-app tail -f storage/logs/laravel.log`