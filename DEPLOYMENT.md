# CapRover Deployment Guide

This guide walks you through deploying the Logje Health Tracking Application to your CapRover server.

## Prerequisites

- CapRover server running at `server.logje.nl`
- MariaDB database container (`mariadb-db`) running on CapRover
- GitHub repository: `mennozweistra/logje-claude`
- Domains: `logje.nl` and `www.logje.nl` configured in your DNS

## Step 1: Create CapRover App

1. Login to your CapRover dashboard at `https://server.logje.nl`
2. Go to **Apps** → **One-Click Apps/Databases** → **Create App**
3. Choose **App Name**: `logje`
4. Select **Has Persistent Data**: `No` (all data is in database)

## Step 2: Configure GitHub Repository

1. In your `logje` app, go to **Deployment** tab
2. Select **Method**: `Deploy from Github/Bitbucket/Gitlab`
3. Enter **Repository**: `https://github.com/mennozweistra/logje-claude`
4. Enter **Branch**: `main`
5. Enter **Username**: `mennozweistra`
6. Generate a **Personal Access Token** in GitHub:
   - Go to GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
   - Generate new token with `repo` scope
   - Copy the token
7. Enter the token in **Password/Token** field
8. Click **Save & Update**

## Step 3: Configure Environment Variables

1. Go to **App Configs** tab
2. Scroll down to **Environment Variables**
3. Add these variables (replace placeholders with actual values):

```
APP_NAME=Logje
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Europe/Amsterdam
APP_URL=https://logje.nl
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=srv-captain--mariadb-db
DB_PORT=3306
DB_DATABASE=logje
DB_USERNAME=[your_mariadb_username]
DB_PASSWORD=[your_mariadb_password]

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## Step 4: Generate APP_KEY

⚠️ **IMPORTANT**: The APP_KEY must be generated securely and never shared.

1. In CapRover, go to your `logje` app
2. Click **Deployment** → **App Logs** 
3. Open a **Terminal** session
4. Run: `php artisan key:generate --show`
5. Copy the generated key (starts with `base64:`)
6. Go back to **App Configs** → **Environment Variables**
7. Add: `APP_KEY=[your_generated_key]`
8. Click **Save & Update**

## Step 5: Initial Deployment

1. Go to **Deployment** tab
2. Click **Force Build and Deploy**
3. Wait for deployment to complete (check logs for any errors)

## Step 6: Database Setup

### Create Database (if not exists)
1. Connect to your MariaDB container
2. Create database: `CREATE DATABASE logje CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
3. Create user and grant permissions (or use existing user)

### Run Migrations
⚠️ **Manual Migration Recommended** for production safety:

1. In CapRover, go to your `logje` app → **Deployment** → **App Logs**
2. Open **Terminal**
3. Run: `php artisan migrate:status` (check current state)
4. Run: `php artisan migrate` (apply migrations)
5. Verify: `php artisan migrate:status` (confirm all migrations ran)

## Step 7: Configure Custom Domains

1. In your `logje` app, go to **HTTP Settings**
2. **Enable HTTPS**: Check this box
3. **Force HTTPS**: Check this box
4. Add **Custom Domains**:
   - `logje.nl`
   - `www.logje.nl`
5. Click **Save & Update**
6. Wait for SSL certificates to be automatically generated

## Step 8: Set Up Auto-Deployment Webhook

1. In CapRover, go to your `logje` app → **Deployment**
2. Copy the **Webhook URL** from the GitHub section
3. Go to your GitHub repository → **Settings** → **Webhooks**
4. Click **Add webhook**
5. Paste the **Payload URL** from CapRover
6. Set **Content type**: `application/json`
7. Select **Just the push event**
8. Check **Active**
9. Click **Add webhook**

## Step 9: Test Deployment

1. Visit `https://logje.nl` to verify the app is running
2. Test user registration and login
3. Test measurement creation and editing
4. Verify all functionality works

## Step 10: Test Auto-Deployment

1. Make a small change to your repository (e.g., update README)
2. Commit and push to `main` branch
3. Check CapRover logs to see if deployment triggers automatically
4. Verify the change appears on the live site

## Production Optimization (Optional)

After successful deployment, you can optimize for production:

```bash
# In CapRover terminal
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Verify MariaDB container name: `srv-captain--mariadb-db`
   - Check database credentials
   - Ensure database exists

2. **APP_KEY Error**:
   - Generate new key: `php artisan key:generate --show`
   - Add to environment variables

3. **Permission Errors**:
   - Check storage directory permissions
   - Ensure `bootstrap/cache` is writable

4. **SSL Certificate Issues**:
   - Wait a few minutes for automatic generation
   - Ensure domains point to your CapRover server

### View Logs:
- CapRover: Apps → logje → Deployment → App Logs
- Laravel: `php artisan log:clear` then check storage/logs/

## Security Notes

- Never commit `.env` files to repository
- Keep APP_KEY secret and secure
- Regularly update dependencies
- Monitor application logs
- Use strong database passwords

## Backup Strategy

- Database: Regular MariaDB backups
- Code: GitHub repository serves as backup
- No persistent file storage needed (all data in database)

---

🎉 **Your Logje Health Tracking Application should now be live at https://logje.nl!**