<?php
/**
 * Database Configuration
 * 
 * Simple configuration to ensure our auth-api.php can connect to the database
 */

return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'mpbh_db',
    'username' => $_ENV['DB_USER'] ?? 'mpbh_user',
    'password' => $_ENV['DB_PASS'] ?? 'mpbh_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
