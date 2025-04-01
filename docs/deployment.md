# Deployment Guide

## Overview

This guide provides instructions for deploying the Mpumalanga Business Hub application to production. The deployment process includes setting up the frontend, backend, and database components on Afrihost Shared Hosting, which is the target hosting environment for this project.

## Prerequisites

Before deployment, ensure you have the following:

1. Afrihost Shared Hosting account with:
   - PHP 8.1+
   - MySQL 8.0 database
   - SSH access (if available)
2. Domain name registered and configured
3. FTP client (such as FileZilla) for file transfers
4. Git for version control
5. Node.js and npm for frontend build process

## Deployment Environment Variables

Create the following environment files before deployment:

### Frontend (.env.production)

```
VITE_API_BASE_URL=https://api.mpbusinesshub.co.za
VITE_APP_ENV=production
```

### Backend (.env)

```
DB_HOST=localhost
DB_NAME=mpbh_prod
DB_USER=your_database_user
DB_PASSWORD=your_database_password

JWT_SECRET=your_secure_jwt_secret
JWT_EXPIRY=86400

API_URL=https://api.mpbusinesshub.co.za
FRONTEND_URL=https://mpbusinesshub.co.za

PAYFAST_MERCHANT_ID=your_merchant_id
PAYFAST_MERCHANT_KEY=your_merchant_key
PAYFAST_SANDBOX=false

MAIL_HOST=mail.mpbusinesshub.co.za
MAIL_PORT=587
MAIL_USERNAME=noreply@mpbusinesshub.co.za
MAIL_PASSWORD=your_mail_password
MAIL_FROM=noreply@mpbusinesshub.co.za
```

## Frontend Deployment

### Building the Frontend

1. Navigate to the client directory:

```bash
cd /path/to/MPBH/client
```

2. Install dependencies:

```bash
npm install
```

3. Create production build:

```bash
npm run build
```

This will generate a `dist` directory containing the compiled assets.

### Uploading to Web Server

1. Using FTP, connect to your Afrihost hosting account
2. Navigate to the public HTML directory (usually `public_html` or `www`)
3. Create a directory for the frontend (e.g., `public_html`)
4. Upload all files from the `dist` directory to this location

### Configuring the Web Server

Create a `.htaccess` file in the root of your frontend directory with the following content:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>

# Cache control for static assets
<FilesMatch "\.(css|js|jpg|jpeg|png|gif|svg|ico)$">
  Header set Cache-Control "max-age=31536000, public"
</FilesMatch>
```

This configuration enables client-side routing with React Router and sets appropriate caching headers for static assets.

## Backend Deployment

### Setting Up the Database

1. Log in to your Afrihost cPanel or hosting control panel
2. Navigate to the MySQL Databases section
3. Create a new database (e.g., `mpbh_prod`)
4. Create a database user with appropriate privileges
5. Import the database schema using the `schema.sql` file:
   - Upload the file via phpMyAdmin, or
   - Use the MySQL command line:
     ```bash
     mysql -u username -p mpbh_prod < schema.sql
     ```

### Uploading Backend Files

1. Using FTP, connect to your Afrihost hosting account
2. Create a directory for the backend outside the web root (e.g., `private/api`)
3. Upload all files from the `server` directory to this location
4. Create the `.env` file with your production environment variables

### Configuring the API Endpoint

Create a subdomain for the API (e.g., `api.mpbusinesshub.co.za`) and set up the following `.htaccess` file in the API directory:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^ index.php [QSA,L]
</IfModule>

# Set CORS headers
<IfModule mod_headers.c>
  Header set Access-Control-Allow-Origin "https://mpbusinesshub.co.za"
  Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
  Header set Access-Control-Allow-Headers "Content-Type, Authorization"
  Header set Access-Control-Allow-Credentials "true"
  
  # Handle preflight OPTIONS requests
  RewriteEngine On
  RewriteCond %{REQUEST_METHOD} OPTIONS
  RewriteRule ^(.*)$ $1 [R=200,L]
</IfModule>
```

## Domain Configuration

1. Log in to your domain registrar account
2. Configure your domain's DNS settings:
   - Point the main domain (`mpbusinesshub.co.za`) to your Afrihost hosting IP address
   - Create an A record for the API subdomain (`api.mpbusinesshub.co.za`) pointing to the same IP address

## SSL Configuration

Secure your website with SSL certificates:

1. Log in to your Afrihost cPanel
2. Navigate to the SSL/TLS section
3. Use Let's Encrypt to generate free SSL certificates for both domains:
   - `mpbusinesshub.co.za`
   - `api.mpbusinesshub.co.za`
4. Ensure that your site is configured to use HTTPS by default

## Cron Jobs

Set up the following cron jobs for maintenance tasks:

1. Log in to your Afrihost cPanel
2. Navigate to the Cron Jobs section
3. Add the following cron jobs:

```bash
# Daily - Update advert status (active/expired)
0 0 * * * /usr/bin/php /path/to/private/api/cron/update_adverts.php

# Daily - Process subscription renewals
0 1 * * * /usr/bin/php /path/to/private/api/cron/process_subscriptions.php

# Weekly - Database backup (Sunday at 2am)
0 2 * * 0 /usr/bin/php /path/to/private/api/cron/backup_database.php
```

## PayFast Integration

To set up PayFast payment processing:

1. Log in to your PayFast merchant account
2. Configure the return URL: `https://mpbusinesshub.co.za/payment/success`
3. Configure the cancel URL: `https://mpbusinesshub.co.za/payment/cancel`
4. Configure the notify URL: `https://api.mpbusinesshub.co.za/payments/notify`
5. Enable API access and generate API keys
6. Update your backend `.env` file with the PayFast credentials

## Post-Deployment Tasks

### Verify Deployment

1. Visit the frontend URL (`https://mpbusinesshub.co.za`) and verify that the site loads correctly
2. Test the API endpoints by accessing `https://api.mpbusinesshub.co.za/businesses`
3. Try registering a new business account
4. Test the login functionality
5. Verify that the dashboard works for authenticated users

### Monitor Logs

Set up log monitoring to catch and address any issues:

1. Configure error logging in PHP:
   ```php
   // In your index.php or bootstrap file
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ini_set('error_log', '/path/to/private/logs/php_errors.log');
   ```

2. Set up regular log checking via cron job or manual review

## Backup Strategy

Implement a comprehensive backup strategy:

1. Database backups:
   - Daily automated backups using the cron job mentioned above
   - Store backups in a secure, off-site location
   
2. File backups:
   - Weekly backup of all application files
   - Use Afrihost's backup service or a third-party solution

3. Verify backup integrity regularly by performing test restores

## Deployment Automation (Optional)

For more streamlined deployments, consider setting up a basic CI/CD pipeline:

1. Create a deployment script:

```bash
#!/bin/bash

# Frontend deployment
cd /path/to/MPBH/client
git pull
npm install
npm run build
rsync -avz --delete dist/ username@hostname:/path/to/public_html/

# Backend deployment
cd /path/to/MPBH/server
git pull
rsync -avz --exclude '.env' --exclude 'logs/' --exclude 'uploads/' ./ username@hostname:/path/to/private/api/
```

2. Run this script manually or integrate with GitHub Actions or other CI/CD tools

## Troubleshooting

### Common Issues

1. **500 Internal Server Error**:
   - Check PHP error logs
   - Verify file permissions (should be 644 for files, 755 for directories)
   - Ensure `.env` file exists and is correctly formatted

2. **API Connection Failures**:
   - Verify CORS settings in the `.htaccess` file
   - Check API subdomain DNS configuration
   - Ensure SSL certificate is correctly installed

3. **Database Connection Issues**:
   - Verify database credentials in the `.env` file
   - Check database server status
   - Ensure database user has necessary privileges

4. **Blank Page on Frontend**:
   - Check browser console for JavaScript errors
   - Verify that the build was successful
   - Ensure `.htaccess` file is correctly configured

### Getting Help

If you encounter issues not covered in this guide:

1. Check the application logs for specific error messages
2. Consult the Afrihost knowledge base for hosting-specific issues
3. Contact the development team for application-specific problems
