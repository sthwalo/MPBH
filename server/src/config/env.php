<?php
/**
 * Environment Variable Configuration
 * Sets up environment variables for the application
 */

// Detect the environment based on hostname
$isProduction = (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'afrihost') !== false);

if ($isProduction) {
    // PRODUCTION: Afrihost CPanel PostgreSQL settings
    $_ENV['DB_HOST'] = 'localhost';  // Afrihost PostgreSQL host
    $_ENV['DB_NAME'] = 'mpbusis6k1d8_mpbh';  // Afrihost database name
    $_ENV['DB_PORT'] = '5432';       // PostgreSQL default port
    $_ENV['DB_USER'] = 'mpbusis6k1d8_sthwalo';  // Afrihost PostgreSQL username
    $_ENV['DB_PASSWORD'] = '&2kj+#~F?xo1'; // Actual Afrihost password
    $_ENV['DB_CONNECTION'] = 'pgsql';
    
    $_ENV['ENVIRONMENT'] = 'production';
} else {
    // DEVELOPMENT: Local database settings
    $_ENV['DB_HOST'] = 'localhost';  // Local PostgreSQL host
    $_ENV['DB_NAME'] = 'postgres';    // Default PostgreSQL database that exists on fresh install
    $_ENV['DB_PORT'] = '5432';       // PostgreSQL default port
    $_ENV['DB_USER'] = 'postgres';   // Default PostgreSQL username
    $_ENV['DB_PASSWORD'] = 'postgres'; // Change to match your local setup
    $_ENV['DB_CONNECTION'] = 'pgsql';
    
    $_ENV['ENVIRONMENT'] = 'development';
    $_ENV['DEVELOPMENT_MODE'] = 'fallback';  // Use fallback data when needed
}

// Common settings
$_ENV['LOG_LEVEL'] = 'debug';

// Log that environment was loaded
error_log("Environment variables loaded for " . $_ENV['ENVIRONMENT'] . " environment");
