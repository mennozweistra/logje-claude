#!/bin/bash

# Docker Development Helper Script for Logje Health Tracker
# This script ensures proper file permissions when running Laravel with Docker

# Get current user ID and group ID
export USER_ID=$(id -u)
export GROUP_ID=$(id -g)
export USER_NAME=$(whoami)

echo "Starting Laravel development environment with Docker..."
echo "User ID: $USER_ID, Group ID: $GROUP_ID, User: $USER_NAME"

# Function to set proper permissions on Laravel directories using Docker
fix_permissions() {
    echo "Fixing Laravel directory permissions using Docker..."
    
    # Use Docker to run permission fixes as root, then change ownership
    docker run --rm -v "$(pwd):/var/www/html" alpine:latest sh -c "
        # Create directories if they don't exist
        mkdir -p /var/www/html/storage/logs
        mkdir -p /var/www/html/storage/app/public
        mkdir -p /var/www/html/storage/framework/cache
        mkdir -p /var/www/html/storage/framework/sessions
        mkdir -p /var/www/html/storage/framework/views
        mkdir -p /var/www/html/bootstrap/cache
        
        # Set ownership to host user
        chown -R $USER_ID:$GROUP_ID /var/www/html/storage/
        chown -R $USER_ID:$GROUP_ID /var/www/html/bootstrap/cache/
        chown -R $USER_ID:$GROUP_ID /var/www/html/public/
        
        # Set proper permissions
        chmod -R 775 /var/www/html/storage/
        chmod -R 775 /var/www/html/bootstrap/cache/
        chmod -R 755 /var/www/html/public/
    "
    
    echo "Permissions fixed using Docker!"
}

# Check command line arguments
case "$1" in
    "up")
        # Fix permissions first
        fix_permissions
        
        # Start containers
        docker compose -f docker-compose.dev.yml up -d
        
        echo "Development environment started!"
        echo "Application: http://localhost:8000"
        echo "PHPMyAdmin: http://localhost:8081"
        ;;
        
    "down")
        docker compose -f docker-compose.dev.yml down
        echo "Development environment stopped!"
        ;;
        
    "build")
        fix_permissions
        docker compose -f docker-compose.dev.yml build --no-cache
        echo "Development containers rebuilt!"
        ;;
        
    "logs")
        docker compose -f docker-compose.dev.yml logs -f app
        ;;
        
    "shell")
        docker compose -f docker-compose.dev.yml exec app bash
        ;;
        
    "artisan")
        shift # Remove 'artisan' from arguments
        docker compose -f docker-compose.dev.yml exec app php artisan "$@"
        ;;
        
    "composer")
        shift # Remove 'composer' from arguments
        docker compose -f docker-compose.dev.yml exec app composer "$@"
        ;;
        
    "npm")
        shift # Remove 'npm' from arguments
        docker compose -f docker-compose.dev.yml exec app npm "$@"
        ;;
        
    "fix-permissions")
        fix_permissions
        ;;
        
    "restart")
        docker compose -f docker-compose.dev.yml restart
        echo "Development environment restarted!"
        ;;
        
    *)
        echo "Laravel Docker Development Helper"
        echo ""
        echo "Usage: $0 {command}"
        echo ""
        echo "Commands:"
        echo "  up              - Start the development environment"
        echo "  down            - Stop the development environment"
        echo "  build           - Rebuild containers with no cache"
        echo "  restart         - Restart the development environment"
        echo "  logs            - Show application logs"
        echo "  shell           - Open bash shell in app container"
        echo "  artisan {cmd}   - Run Laravel artisan commands"
        echo "  composer {cmd}  - Run composer commands"
        echo "  npm {cmd}       - Run npm commands"
        echo "  fix-permissions - Fix Laravel directory permissions"
        echo ""
        echo "Examples:"
        echo "  $0 up"
        echo "  $0 artisan migrate"
        echo "  $0 composer install"
        echo "  $0 npm run dev"
        ;;
esac