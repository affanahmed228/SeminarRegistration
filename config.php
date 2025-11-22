<?php
// Database configuration for localhost
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'seminarregistration');

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Create uploads directory if it doesn't exist
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}

// Test connection (optional)
try {
    $testConn = getDBConnection();
    // echo "Database connected successfully";
    $testConn->close();
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
}
?>