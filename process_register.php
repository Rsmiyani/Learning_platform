<?php
require_once 'config/database.php';
initSession();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// Get form data
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($first_name)) {
    $errors[] = "First name is required";
}

if (empty($last_name)) {
    $errors[] = "Last name is required";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required";
}

if (empty($role) || !in_array($role, ['trainee', 'trainer', 'admin'])) {
    $errors[] = "Please select a valid role";
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// If validation fails
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: register.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email already registered. Please login instead.";
        header('Location: register.php');
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password, role, status) 
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $role]);
    
    // Success
    $_SESSION['success'] = "Registration successful! Please login.";
    header('Location: login.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    $_SESSION['error'] = "Registration failed. Please try again.";
    header('Location: register.php');
    exit;
}
?>
