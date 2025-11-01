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
$points = $_GET['points'] ?? null;

if (!$user_id || $points === null) {
    $_SESSION['error'] = "Invalid parameters";
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header('Location: index.php');
        exit;
    }
    
    // Update points
    $stmt = $pdo->prepare("
        INSERT INTO user_points (user_id, total_points, level)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE total_points = total_points + ?
    ");
    $stmt->execute([$user_id, $points, $points]);
    
    // Update level based on new points
    updateUserLevel($pdo, $user_id);
    
    // Get new points
    $stmt = $pdo->prepare("SELECT total_points FROM user_points WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $new_points = $stmt->fetch()['total_points'];
    
    $_SESSION['success'] = "Successfully " . ($points > 0 ? "added $points" : "removed " . abs($points)) . " points to {$user['first_name']} {$user['last_name']}. New total: $new_points points";
    
} catch (PDOException $e) {
    error_log("Points management error: " . $e->getMessage());
    $_SESSION['error'] = "Error updating points: " . $e->getMessage();
}

header('Location: index.php');
exit;
?>
