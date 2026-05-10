<?php
// src/Database.php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: "localhost";
        $this->db_name = getenv('DB_NAME') ?: "recruitment_db";
        $this->username = getenv('DB_USER') ?: "admin.user";
        $this->password = getenv('DB_PASS') ?: "Admin123";
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->conn;
        } catch(PDOException $e) {
            // Instead of just logging, stop the script so you can see the error
            die("Database Connection failed: " . $e->getMessage()); 
        }
    }
}