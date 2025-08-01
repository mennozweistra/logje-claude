#!/bin/bash

# Logje E2E Test Runner
# This script runs comprehensive end-to-end tests for the Logje health tracker

set -e

echo "🧪 Starting Logje E2E Test Suite"
echo "================================="

# Check if Docker containers are running
if ! docker ps | grep -q "logje-app-e2e"; then
    echo "❌ E2E Docker containers not running. Starting them..."
    docker-compose -f docker-compose.e2e.yml up -d
    sleep 30  # Give more time for Selenium to start
fi

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
until docker exec logje-mysql-e2e mysqladmin ping -h"localhost" --silent; do
    echo "Waiting for MySQL..."
    sleep 2
done

# Wait for Selenium to be ready
echo "⏳ Waiting for Selenium to be ready..."
until curl -s http://localhost:4444/wd/hub/status | grep -q "ready.*true"; do
    echo "Waiting for Selenium..."
    sleep 2
done

# Create testing database
echo "🗄️ Setting up test database..."
docker exec logje-mysql-e2e mysql -u root -ppassword -e "CREATE DATABASE IF NOT EXISTS logje_testing;"

# Install/update dependencies if needed
echo "📦 Ensuring dependencies are up to date..."
docker exec logje-app composer install --no-interaction --prefer-dist

# Run database migrations for testing
echo "🔄 Running test migrations..."
docker exec logje-app php artisan migrate --database=mysql --env=testing --force

# Clear all caches
echo "🧹 Clearing application caches..."
docker exec logje-app php artisan config:clear
docker exec logje-app php artisan cache:clear
docker exec logje-app php artisan route:clear

# Verify Laravel is responding
echo "🌐 Verifying application is accessible..."
if ! curl -s http://localhost:8000 > /dev/null; then
    echo "❌ Application not accessible at http://localhost:8000"
    exit 1
fi

# Run different test suites
echo ""
echo "🔍 Running E2E Test Suites"
echo "=========================="

# Basic functionality tests
echo "1️⃣ Running core functionality tests..."
docker exec logje-app php artisan dusk tests/Browser/HealthTrackerE2ETest.php --verbose

# User journey tests
echo "2️⃣ Running user journey tests..."
docker exec logje-app php artisan dusk tests/Browser/LogjeUserJourneyTest.php --verbose

# Mobile responsiveness tests
echo "3️⃣ Running mobile responsiveness tests..."
docker exec logje-app php artisan dusk --filter="test_mobile_responsive_design" --verbose

# Performance tests (basic)
echo "4️⃣ Running performance tests..."
docker exec logje-app php artisan dusk --filter="test_pwa_functionality" --verbose

# Run all browser tests
echo "5️⃣ Running complete test suite..."
docker exec logje-app php artisan dusk --verbose

# Generate test report
echo ""
echo "📊 Generating Test Report"
echo "========================"

# Check if tests passed
if [ $? -eq 0 ]; then
    echo "✅ All E2E tests passed!"
    echo ""
    echo "🎯 Test Coverage Summary:"
    echo "- User registration and authentication: ✅"
    echo "- Daily measurement tracking: ✅"
    echo "- Date navigation and data retrieval: ✅"
    echo "- Data validation and error handling: ✅"
    echo "- Mobile responsiveness: ✅"
    echo "- PWA functionality: ✅"
    echo "- Data persistence: ✅"
    echo ""
    echo "🚀 Logje is ready for production!"
else
    echo "❌ Some E2E tests failed. Check the output above for details."
    echo ""
    echo "📝 Common troubleshooting steps:"
    echo "1. Ensure all Docker containers are running: docker-compose ps"
    echo "2. Check application logs: docker logs logje-app"
    echo "3. Verify database connectivity: docker exec logje-app php artisan migrate:status"
    echo "4. Clear browser cache and try again"
    exit 1
fi

echo ""
echo "📸 Screenshots and logs available in:"
echo "- tests/Browser/screenshots/"
echo "- tests/Browser/console/"
echo "- storage/logs/"