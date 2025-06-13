<?php
require_once 'config.php';

// Create database connection
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Get database connection
function getDB() {
    static $conn;
    if ($conn === NULL) {
        $conn = connectDB();
    }
    return $conn;
}
?>