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

# Rate limiting configuration
RATE_LIMIT_ENABLED=true
RATE_LIMIT_REQUESTS=60
RATE_LIMIT_PER_MINUTE=1

# Admin approval settings
ADMIN_APPROVAL_REQUIRED=true
ADMIN_NOTIFICATION_EMAIL=admin@mpbusinesshub.co.za

# Search configuration
SEARCH_RESULTS_PER_PAGE=20
SEARCH_LOG_ENABLED=true
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

# Rate limiting (IP-based)
<IfModule mod_rewrite.c>
  RewriteEngine On
  
  # Exclude specific endpoints from rate limiting (e.g., public business listings)
  RewriteCond %{REQUEST_URI} !^/api/businesses/public
  
  # Apply rate limiting to API calls
  RewriteCond %{HTTP:X-Forwarded-For} !^$
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule .* - [E=RATE_LIMIT:1]
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

# Daily - Reset rate limiting counters
0 0 * * * /usr/bin/php /path/to/private/api/cron/reset_rate_limits.php

# Weekly - Clean up search logs (keep last 90 days)
0 3 * * 0 /usr/bin/php /path/to/private/api/cron/clean_search_logs.php

# Daily - Generate admin statistics report
0 5 * * * /usr/bin/php /path/to/private/api/cron/generate_admin_stats.php
```

## PayFast Integration

To set up PayFast payment processing:

1. Log in to your PayFast merchant account
2. Configure the return URL: `https://mpbusinesshub.co.za/payment/success`
3. Configure the cancel URL: `https://mpbusinesshub.co.za/payment/cancel`
4. Configure the notify URL: `https://api.mpbusinesshub.co.za/api/payments/notify`
5. Enable Instant Transaction Notifications (ITN)
6. Whitelist the server IP address in your PayFast merchant account

## Service Layer Configuration

The application uses a service layer architecture to encapsulate business logic. Ensure the following directories have proper permissions for the web server user:

```bash
chmod -R 755 /path/to/private/api/src/services
chmod -R 755 /path/to/private/api/src/controllers
chmod -R 755 /path/to/private/api/src/middleware
```

## Rate Limiting Configuration

The application implements rate limiting to prevent abuse. The rate limiting is configured at two levels:

1. **Server Level**: Using the `.htaccess` configuration
2. **Application Level**: Using the PHP middleware

To configure rate limiting limits for different endpoints, edit the `config/rate_limits.php` file:

```php
return [
    'default' => [
        'requests' => 60,  // Number of requests
        'per_minute' => 1,  // Time window in minutes
    ],
    'search' => [
        'requests' => 30,
        'per_minute' => 1,
    ],
    'admin' => [
        'requests' => 120,
        'per_minute' => 1,
    ],
];
```

## Search Service Configuration

The application uses a search service for advanced business searching. To configure the search service:

1. Ensure the `search_logs` table exists in the database
2. Configure search parameters in the `.env` file (as shown above)
3. Run the initial search index build script:

```bash
/usr/bin/php /path/to/private/api/scripts/build_search_index.php
```

## Admin Approval Workflow

The admin approval workflow requires configuration:

1. Set up admin users in the database with the appropriate role
2. Configure the admin notification email in the `.env` file
3. Ensure the admin dashboard is accessible only to users with admin privileges

## Monitoring and Maintenance

### Error Logging

Configure error logging to capture and log application errors:

1. Create a log directory:

```bash
mkdir -p /path/to/private/api/logs
chmod 755 /path/to/private/api/logs
```

2. Configure log settings in the `.env` file:

```
LOG_LEVEL=error  // Options: debug, info, warning, error
LOG_FILE=/path/to/private/api/logs/app.log
```

### Performance Monitoring

Consider setting up basic performance monitoring:

1. Configure PHP slow query logging
2. Set up a cron job to analyze logs for performance issues

```bash
// Weekly - Analyze logs for performance issues
0 4 * * 0 /usr/bin/php /path/to/private/api/scripts/analyze_performance.php
```

## Conclusion

This deployment guide covers the essential steps for deploying the Mpumalanga Business Hub application to Afrihost Shared Hosting. Follow these instructions carefully to ensure a successful deployment.

For any issues or questions related to deployment, refer to the troubleshooting section in the project documentation or contact the development team.

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
