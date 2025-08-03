# CapRover Deployment Guide

This guide walks you through deploying the Logje Health Tracking Application to your CapRover server.

## Prerequisites

- CapRover server running at `server.logje.nl`
- MariaDB database container (`maria-db`) running on CapRover
- GitHub repository: `mennozweistra/logje-claude`
- Domains: `logje.nl` and `www.logje.nl` configured in your DNS

## Step 1: Create CapRover App

1. Login to your CapRover dashboard at `https://server.logje.nl`
2. Go to **Apps** and click the **Create New App** button (or **+** button)
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
   - Click **Generate new token (classic)**
   - Set **Expiration**: Choose appropriate duration (e.g., 90 days, 1 year, or no expiration)
   - **Select these specific scopes/permissions**:
     - ✅ **repo** (Full control of private repositories) - Required for repository access
     - ✅ **admin:repo_hook** (Full control of repository hooks) - Required for webhook management
   - Click **Generate token**
   - Copy the token immediately (you won't see it again)
7. Enter the token in the **Password** field
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
APP_URL=https://logje.server.logje.nl
LOG_LEVEL=error

# Force HTTPS for all URLs (REQUIRED - fixes mixed content errors)
FORCE_HTTPS=true
ASSET_URL=https://logje.server.logje.nl

DB_CONNECTION=mysql
DB_HOST=srv-captain--maria-db
DB_PORT=3306
DB_DATABASE=logje
DB_USERNAME=[your_mariadb_username]
DB_PASSWORD=[your_mariadb_password]

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

## Step 4: Initial Deployment from Repository

⚠️ **IMPORTANT**: You must deploy from the repository first before generating APP_KEY.

1. Go to **Deployment** tab
2. Click **Force Build and Deploy**
3. Wait for deployment to complete (check logs for any errors)
4. This builds your Logje container from the GitHub repository

## Step 5: Generate APP_KEY

⚠️ **IMPORTANT**: The APP_KEY must be generated securely and never shared.

1. **SSH to your server and find the Logje container:**
   ```bash
   ssh your-server
   docker ps
   # Look for a container with name containing "logje" or "srv-captain--logje"
   ```
2. **Generate the APP_KEY:**
   ```bash
   # Generate APP_KEY with correct cipher (must be base64 encoded for AES-256-CBC)
   docker exec -it <logje-container-id> bash -c "cd /var/www/html && php artisan key:generate --show"
   
   # Alternative - enter container first:
   docker exec -it <logje-container-id> bash
   # Then: cd /var/www/html && php artisan key:generate --show
   
   # IMPORTANT: The generated key should start with "base64:" for AES-256-CBC cipher
   # Example format: base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
   ```
2. Copy the generated key (starts with `base64:`)
3. In CapRover, go to your `logje` app → **App Configs** → **Environment Variables**
4. Add: `APP_KEY=[your_generated_key]`
5. Click **Save & Update**

## Step 6: Redeploy with APP_KEY

1. Go to **Deployment** tab
2. Click **Force Build and Deploy**
3. Wait for deployment to complete (check logs for any errors)

## Step 7: Database Setup

### Create Database and User
1. **SSH to your CapRover server:**
   ```bash
   ssh your-server
   ```
2. **Find your MariaDB container:**
   ```bash
   docker ps | grep maria-db
   ```
3. **Connect to MariaDB container:**
   ```bash
   docker exec -it <maria-db-container-id> mysql -u root -p
   ```
4. Enter your root password when prompted
5. Create the database:
   ```sql
   CREATE DATABASE logje CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
6. Create a dedicated user for Logje:
   ```sql
   CREATE USER 'logje_user'@'%' IDENTIFIED BY 'your_secure_password_here';
   ```
7. Grant permissions to the user:
   ```sql
   GRANT ALL PRIVILEGES ON logje.* TO 'logje_user'@'%';
   FLUSH PRIVILEGES;
   ```
8. Verify the setup:
   ```sql
   SHOW DATABASES;
   SELECT User, Host FROM mysql.user WHERE User = 'logje_user';
   ```
9. Exit MariaDB: `EXIT;`

### Update Environment Variables
Now update your CapRover environment variables with the database credentials:
- `DB_DATABASE=logje`
- `DB_USERNAME=logje_user`
- `DB_PASSWORD=your_secure_password_here`

### Database Migrations

✅ **Automatic Migrations**: Database migrations now run automatically during deployment!

The application startup script will:
1. **Wait for database connection** (up to 30 seconds)
2. **Run migrations automatically** with `php artisan migrate --force`
3. **Continue startup** even if migrations fail (with warning)
4. **Log all migration activity** in the deployment logs

**To monitor migrations:**
1. In CapRover, go to your `logje` app → **Deployment** → **App Logs**
2. Look for migration messages in the startup logs:
   ```
   Starting Laravel deployment...
   Checking database connection...
   Database connection successful
   Running database migrations...
   Migrations completed successfully
   Starting Apache web server...
   ```

**Manual migration (if needed):**
If you need to run migrations manually or check status:
1. Open **Terminal** in CapRover app
2. Run: `php artisan migrate:status` (check current state)
3. Run: `php artisan migrate --force` (apply migrations manually)

## Step 8: Configure Custom Domains

1. In your `logje` app, go to **HTTP Settings**
2. **Enable HTTPS**: Check this box
3. **Force HTTPS**: Check this box
4. Add **Custom Domains**:
   - `logje.nl`
   - `www.logje.nl`
5. Click **Save & Update**
6. Wait for SSL certificates to be automatically generated

## Step 9: Set Up Auto-Deployment Webhook

1. In CapRover, go to your `logje` app → **Deployment**
2. Copy the **Webhook URL** from the GitHub section
3. Go to your GitHub repository → **Settings** → **Webhooks**
4. Click **Add webhook**
5. Paste the **Payload URL** from CapRover
6. Set **Content type**: `application/json`
7. Select **Just the push event**
8. Check **Active**
9. Click **Add webhook**

## Step 10: Test Deployment

1. Visit `https://logje.nl` to verify the app is running
2. Test user registration and login
3. Test measurement creation and editing
4. Verify all functionality works

## Step 11: Test Auto-Deployment

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
   - Verify MariaDB container name: `srv-captain--maria-db`
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