<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_GET['user_id'] ?? null;
$status = $_GET['status'] ?? null;

if (!$user_id || !$status || !in_array($status, ['active', 'inactive'])) {
    $_SESSION['error'] = "Invalid parameters";
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT first_name, last_name, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header('Location: index.php');
        exit;
    }
    
    // Prevent deactivating admin accounts
    if ($user['role'] === 'admin') {
        $_SESSION['error'] = "Cannot modify admin account status";
        header('Location: index.php');
        exit;
    }
    
    // Update user status
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    $stmt->execute([$status, $user_id]);
    
    $action = $status === 'active' ? 'activated' : 'deactivated';
    $_SESSION['success'] = "Successfully $action {$user['first_name']} {$user['last_name']}'s account";
    
} catch (PDOException $e) {
    error_log("Status toggle error: " . $e->getMessage());
    $_SESSION['error'] = "Error updating status: " . $e->getMessage();
}

header('Location: index.php');
exit;
?>
