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
        // Load from environment variables
        $this->host = $_ENV['DB_HOST'];
        $this->db_name = $_ENV['DB_NAME'];
        $this->db_port = $_ENV['DB_PORT'] ?? '5432';
        $this->username = $_ENV['DB_USER'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->connection_string = $_ENV['DB_CONNECTION'] ?? 'pgsql';
    }

    /**
     * Get the database connection
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;

        try {
            // Create PostgreSQL connection string
            $dsn = "{$this->connection_string}:host={$this->host};port={$this->db_port};dbname={$this->db_name}";
            
            // Create connection with error mode set to exceptions
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Set UTF-8 encoding
            $this->conn->exec("SET NAMES 'UTF8'");
            
            // Log successful connection if in debug mode
            if (isset($_ENV['LOG_LEVEL']) && $_ENV['LOG_LEVEL'] == 'debug') {
                error_log("Database connection established successfully");
            }
        } catch(PDOException $e) {
            // Log connection error
            error_log("Database Connection Error: " . $e->getMessage());
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
