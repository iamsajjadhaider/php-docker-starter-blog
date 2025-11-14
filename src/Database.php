<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Fetch variables from the environment set in docker-compose.yml
        $this->host = getenv('MYSQL_HOST');
        $this->db_name = getenv('MYSQL_DATABASE');
        $this->username = getenv('MYSQL_USER');
        $this->password = getenv('MYSQL_PASSWORD');
    }

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Use PDO for a modern, secure approach
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Set error mode to exception for better error handling
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // In a real application, you would log this error, not just echo it
            echo "Connection error: " . $exception->getMessage();
            // You might also want to exit here if the connection is critical
        }

        return $this->conn;
    }
} // <-- This closing brace was the fix for the Parse Error
