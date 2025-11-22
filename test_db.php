<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "✅ Database connected successfully!<br>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM registrations");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Table exists. Total registrations: " . $row['count'];
    } else {
        echo "❌ Table query failed";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}
?>