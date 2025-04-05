# Deployment Guide

This guide outlines the process for deploying the Mpumalanga Business Hub application to Afrihost Shared Hosting.

## Prerequisites
- Afrihost Shared Hosting account
- PostgreSQL database (provided by Afrihost)
- FTP access credentials
- SSH access (if available)

## Database Setup

1. **PostgreSQL Database Connection Details**:
   - Host: localhost
   - Database Name: mpbusis6k1d8_mpbh
   - Username: mpbusis6k1d8_sthwalo
   - Password: (stored securely)
   - Port: 5432

2. **Initialize Database Tables**:
   - Upload and execute `setup-database.php` script to create required tables
   - Verify table creation with phpPgAdmin or similar PostgreSQL administration tool

## Backend Deployment

1. **Environment Configuration**:
   - Create a `.env` file based on `.env.example` with appropriate values
   - Set `ENVIRONMENT=production` to enable production mode
   - Update database credentials to match Afrihost PostgreSQL settings

2. **File Upload**:
   - Upload server directory content to the public_html directory via FTP
   - Ensure correct file permissions:
     - PHP files: 644
     - Directories: 755
     - Configuration files: 600

3. **Server Configuration**:
   - Create or update `.htaccess` file to handle routing
   - Configure PHP settings if necessary (memory_limit, upload_max_filesize, etc.)

## Frontend Deployment

1. **Build Production Assets**:
   - Navigate to the client directory
   - Run `npm run build` to generate optimized production assets
   - Verify build output in the `dist` directory

2. **Upload Frontend Assets**:
   - Upload the contents of the `dist` directory to a subdirectory or separate domain
   - Ensure all assets are properly referenced with correct paths

3. **API Configuration**:
   - Update API base URL in `src/config/api.js` to point to the production endpoint
   - Rebuild if necessary

## Post-Deployment Verification

1. **Functionality Testing**:
   - Test user registration and login
   - Verify business data is correctly displayed
   - Check search and filtering functionality
   - Test dashboard features

2. **Performance Optimization**:
   - Enable browser caching for static assets
   - Verify GZIP compression is enabled
   - Check for any slow API responses and optimize if necessary

## Monitoring and Maintenance

1. **Error Logging**:
   - Configure error logging to a dedicated file
   - Set up log rotation to prevent excessive disk usage

2. **Backup Strategy**:
   - Regular database backups (daily recommended)
   - File system backups for uploaded content
   - Store backups in secure, offsite location

3. **Updates and Patches**:
   - Regular security updates for dependencies
   - Planned maintenance windows for major updates
