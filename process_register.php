<?php
require_once 'config/database.php';
require_once 'config/email.php';
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
$role = 'trainee'; // Always set as trainee - users can request trainer role later
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
    $user_id = $pdo->lastInsertId();
    
    // Save interests for trainees
    if ($role === 'trainee' && isset($_POST['interests']) && is_array($_POST['interests'])) {
        $interests = $_POST['interests'];
        
        foreach ($interests as $interest) {
            $stmt = $pdo->prepare("INSERT INTO user_interests (user_id, interest_name) VALUES (?, ?)");
            $stmt->execute([$user_id, trim($interest)]);
        }
    }
    
    // Send welcome email
    $mailer = getMailer();
    $email_sent = $mailer->sendWelcomeEmail($email, $first_name, ucfirst($role));
    
    if ($email_sent) {
        error_log("Welcome email sent successfully to: " . $email);
    } else {
        error_log("Failed to send welcome email to: " . $email);
    }
    
    // Success
    $_SESSION['success'] = "Registration successful! Please check your email and login.";
    header('Location: login.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    $_SESSION['error'] = "Registration failed. Please try again.";
    header('Location: register.php');
    exit;
}
?>
