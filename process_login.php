<?php
require_once 'config/database.php';
initSession();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form data
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validation
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Email and password are required";
    header('Location: login.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get user by email
    $stmt = $pdo->prepare("
        SELECT user_id, first_name, last_name, email, password, role, status 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Check if user exists
    if (!$user) {
        $_SESSION['error'] = "Invalid email or password";
        header('Location: login.php');
        exit;
    }
    
    // Check if account is active
    if ($user['status'] !== 'active') {
        $_SESSION['error'] = "Your account is inactive. Please contact support.";
        header('Location: login.php');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Invalid email or password";
        header('Location: login.php');
        exit;
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    // Remember me functionality (optional - for later)
    if ($remember) {
        // Set cookie for 30 days
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), "/");
    }
    
    // Redirect based on role
    switch ($user['role']) {
        case 'admin':
            header('Location: dashboard/admin/index.php');
            break;
        case 'trainer':
            header('Location: dashboard/trainer/index.php');
            break;
        case 'trainee':
            header('Location: dashboard/trainee/index.php');
            break;
        default:
            header('Location: dashboard/index.php');
    }
    exit;
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error'] = "Login failed. Please try again.";
    header('Location: login.php');
    exit;
}
?>
