<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Log received data
error_log("=== REGISTRATION REQUEST ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Get form data with basic validation
$fullName = trim($_POST['fullName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$company = trim($_POST['company'] ?? '');
$position = trim($_POST['position'] ?? '');
$seminarTopic = trim($_POST['seminarTopic'] ?? '');
$dietary = trim($_POST['dietary'] ?? 'None');
$comments = trim($_POST['comments'] ?? '');
$photoData = $_POST['photoData'] ?? '';

// Basic validation
if (empty($fullName) || strlen($fullName) < 2) {
    $response['message'] = 'Name must be at least 2 characters';
    echo json_encode($response);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Valid email is required';
    echo json_encode($response);
    exit;
}

if (empty($phone)) {
    $response['message'] = 'Phone number is required';
    echo json_encode($response);
    exit;
}

if (empty($seminarTopic)) {
    $response['message'] = 'Please select a seminar topic';
    echo json_encode($response);
    exit;
}

// Connect to database and save data
try {
    require_once 'config.php';
    $conn = getDBConnection();
    
    // Handle photo data if provided
    $photoFilename = null;
    if (!empty($photoData)) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Convert base64 to image file
        $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $imageData = base64_decode($photoData);
        
        $filename = uniqid() . '.jpg';
        $filepath = $uploadDir . $filename;
        
        if (file_put_contents($filepath, $imageData)) {
            $photoFilename = $filename;
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO registrations (full_name, email, phone, company, position, seminar_topic, dietary_requirements, comments, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("sssssssss", 
        $fullName,
        $email,
        $phone,
        $company,
        $position,
        $seminarTopic,
        $dietary,
        $comments,
        $photoFilename
    );
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Registration successful! Welcome $fullName. Your registration ID: " . $stmt->insert_id;
        $response['registration_id'] = $stmt->insert_id;
    } else {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $response['message'] = 'Registration failed: ' . $e->getMessage();
}

echo json_encode($response);
?>