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

        try {
            // Create PostgreSQL connection string - properly formatted for PostgreSQL
            $dsn = "{$this->connection_string}:host={$this->host};dbname={$this->db_name};port={$this->db_port}";
            error_log("Connecting to PostgreSQL database with DSN: {$dsn}");
            
            // Create connection with error mode set to exceptions
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Set UTF-8 encoding
            $this->conn->exec("SET NAMES 'UTF8'");
            
            // Log successful connection
            error_log("PostgreSQL database connection established successfully");
        } catch(PDOException $e) {
            // Log connection error
            error_log("PostgreSQL Database Connection Error: " . $e->getMessage());
        }
        
        return $this->conn;
    }

    /**
     * Get active connection or create new one
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn === null) {
            return $this->connect();
        }
        return $this->conn;
    }
}
