<?php
include_once 'config.php';

header('Content-Type: application/json'); // Ensure JSON response

if ($conn->connect_error) {
    die(json_encode(['message' => 'error', 'error' => 'Database connection failed']));
}

// Check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'tbl_users'");
if ($result->num_rows === 0) {
    echo json_encode(['message' => 'error', 'error' => 'ERROR: Users table does not exist.']);
    exit;
}

// Get form data
$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$email = trim($_POST['email']);   
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validate input
if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['message' => 'error', 'error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['message' => 'error', 'error' => 'Invalid email format']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['message' => 'error', 'error' => 'Passwords do not match']);
    exit;
}

// Check if email already exists
$emailStmt = $conn->prepare("SELECT email FROM tbl_users WHERE email = ?");
$emailStmt->bind_param("s", $email);
$emailStmt->execute();
$emailStmt->store_result();

if ($emailStmt->num_rows > 0) {
    echo json_encode(['message' => 'error', 'error' => 'Email already exists.']);
    exit;
}
$emailStmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert user into database
$stmt = $conn->prepare("INSERT INTO tbl_users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['message' => 'error', 'error' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['error' => '', 'redirectUrl' => 'home.php']);
} else {
    echo json_encode(['error' => 'Registration failed. Try again.']);
}


$stmt->close();
$conn->close();
?>
