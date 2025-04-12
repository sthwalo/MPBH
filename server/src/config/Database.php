<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database Connection Class
 * Handles connection to PostgreSQL database
 */
class Database {
    private $host;
    private $db_name;
    private $db_port;
    private $username;
    private $password;
    private $conn;
    private $connection_string;

    /**
     * Constructor - initializes database connection parameters
     */
    public function __construct() {
        // Load from environment variables with fallback to development defaults
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'mpbh_db';
        $this->db_port = $_ENV['DB_PORT'] ?? '5432';
        $this->username = $_ENV['DB_USER'] ?? 'postgres';
        $this->password = $_ENV['DB_PASSWORD'] ?? 'postgres';
        $this->connection_string = $_ENV['DB_CONNECTION'] ?? 'pgsql';
        
        // For development purposes - log connection parameters
        error_log("Database connection parameters: {$this->connection_string}:host={$this->host};port={$this->db_port};dbname={$this->db_name}");
    }

    /**
     * Get the database connection
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;
        $maxRetries = 3;
        $retryDelay = 2; // seconds
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                // Use environment variables with fallbacks
                $dsn = sprintf(
                    "%s:host=%s;port=%s;dbname=%s;connect_timeout=10;sslmode=require;options='--client_encoding=UTF8'",
                    'pgsql',
                    $_ENV['DB_HOST'] ?? 'localhost',
                    $_ENV['DB_PORT'] ?? '5432',
                    $_ENV['DB_NAME'] ?? 'mpbusis6k1d8_mpbh'
                );
                
                $options = [
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_CASE => PDO::CASE_NATURAL
                ];

                $this->conn = new PDO(
                    $dsn, 
                    $_ENV['DB_USER'] ?? 'mpbusis6k1d8_sthwalo',
                    $_ENV['DB_PASSWORD'] ?? 'Password123',
                    $options
                );

                // Set query timeout
                $this->conn->exec("SET statement_timeout = 30000"); // 30 seconds

                error_log("Database connection established successfully on attempt $attempt");
                return $this->conn;

            } catch(PDOException $e) {
                error_log("Database Connection Attempt $attempt Failed: " . $e->getMessage());
                
                if ($attempt < $maxRetries) {
                    error_log("Retrying connection in $retryDelay seconds...");
                    sleep($retryDelay);
                } else {
                    error_log("All connection attempts failed");
                    return null;
                }
            }
        }
        
        return null;
    }

    /**
     * Get active connection or create new one
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }
}
