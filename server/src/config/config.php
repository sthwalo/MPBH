<?php

use Dotenv\Dotenv;
use App\Helpers\ResponseHelper;

/**
 * Load environment variables
 */
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

/**
 * Database Configuration
 */
return [
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_NAME'] ?? 'mpbh_db',
        'username' => $_ENV['DB_USER'] ?? 'mpbh_user',
        'password' => $_ENV['DB_PASS'] ?? 'mpbh_password',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'security' => [
        'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key',
        'password_hash_cost' => $_ENV['PASSWORD_HASH_COST'] ?? 10,
        'csrf_secret' => $_ENV['CSRF_SECRET'] ?? 'your-csrf-secret',
    ],
    'api' => [
        'version' => '1.0',
        'rate_limit' => [
            'limit' => 100,
            'window' => 60, // seconds
        ],
    ],
    'storage' => [
        'upload_path' => $_ENV['UPLOAD_PATH'] ?? __DIR__ . '/../../public/uploads',
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'max_size' => 5 * 1024 * 1024, // 5MB
    ],
    'cache' => [
        'driver' => $_ENV['CACHE_DRIVER'] ?? 'redis',
        'ttl' => 3600, // 1 hour
    ],
    'external_services' => [
        'email_provider' => $_ENV['EMAIL_PROVIDER'] ?? 'sendgrid',
        'storage_provider' => $_ENV['STORAGE_PROVIDER'] ?? 'aws-s3',
    ],
    'frontend' => [
        'url' => $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000',
    ],
];
