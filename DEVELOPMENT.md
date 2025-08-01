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

### Unit Tests (PHPUnit)
```bash
docker exec logje-app php artisan test                    # Run all tests
docker exec logje-app php artisan test --testsuite=Unit   # Unit tests only
docker exec logje-app php artisan test --testsuite=Feature # Feature tests only
docker exec logje-app php artisan test --filter="TestName" # Specific test
```

### Alternative Test Runner (Pest)
```bash
docker exec logje-app ./vendor/bin/pest                   # Run all Pest tests
docker exec logje-app ./vendor/bin/pest --unit            # Unit tests only
docker exec logje-app ./vendor/bin/pest --feature         # Feature tests only
```

### E2E Tests with Laravel Dusk

#### Prerequisites
```bash
# Start ChromeDriver (run on host machine, not in Docker)
./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 &

# Check if ChromeDriver is running
curl -s http://localhost:9515/status
```

#### Run Dusk Tests
```bash
php artisan dusk                                    # Run all browser tests
php artisan dusk tests/Browser/WelcomePageTest.php # Run specific test file
php artisan dusk --filter="test_name"              # Run specific test
php artisan dusk --browse                          # Keep browser open after tests
```

### E2E Tests with MCP Playwright (Interactive)
These run in Claude Code conversation, not command line:
```
mcp__playwright__browser_navigate http://localhost:8000
mcp__playwright__browser_take_screenshot
mcp__playwright__browser_click "button"
mcp__playwright__browser_type "input" "text"
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
./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 & # Start ChromeDriver for tests
```

### Daily Shutdown
```bash
docker-compose down                                  # Stop containers
pkill chromedriver                                   # Stop ChromeDriver
```

### Run All Tests
```bash
docker exec logje-app php artisan test              # Unit/Feature tests
php artisan dusk                                     # E2E tests (requires ChromeDriver)
```

## Troubleshooting

### Common Issues
```bash
# If containers won't start
docker-compose down -v && docker-compose up -d

# If tests fail with connection errors
docker exec logje-app php artisan migrate --env=testing

# If frontend assets aren't loading
docker exec logje-app npm run build

# If ChromeDriver connection fails
pkill chromedriver && ./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 &
```

### Log Files
- Laravel logs: `docker exec logje-app tail -f storage/logs/laravel.log`
- Test screenshots: `tests/Browser/screenshots/`
- Dusk console logs: `tests/Browser/console/`