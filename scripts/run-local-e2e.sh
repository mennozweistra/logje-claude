#!/bin/bash

# Simple Local E2E Test Runner for Logje
# Uses your local Chrome browser and the Docker MySQL database

set -e

echo "🧪 Running Logje E2E Tests Locally"
echo "================================="

# Check if Laravel app is running in Docker
if ! docker ps | grep -q "logje-app"; then
    echo "❌ Laravel app not running. Starting Docker containers..."
    docker-compose up -d
    sleep 10
fi

# Wait for app to be ready
echo "⏳ Waiting for Laravel app..."
until curl -s http://localhost:8000 > /dev/null; do
    echo "Waiting for app..."
    sleep 2
done

# Make sure we have ChromeDriver
echo "🔧 Setting up ChromeDriver..."
docker exec logje-app php artisan dusk:chrome-driver --detect

# Run a simple test first
echo "🎯 Running basic welcome page test..."
docker exec logje-app php artisan dusk tests/Browser/ExampleTest.php

echo "✅ E2E testing is ready!"
echo ""
echo "💡 Tips:"
echo "- Add --without-tty to see browser windows: docker exec logje-app php artisan dusk --without-tty"
echo "- Add --browse to keep browser open after tests: docker exec logje-app php artisan dusk --browse"
echo "- Screenshots saved to: tests/Browser/screenshots/"