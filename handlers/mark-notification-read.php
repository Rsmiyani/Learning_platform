<?php
require_once '../config/database.php';
initSession();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$notification_id = $data['notification_id'] ?? null;
$mark_all = $data['mark_all'] ?? false;

try {
    $pdo = getDBConnection();
    
    if ($mark_all) {
        // Mark all notifications as read for this user
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else if ($notification_id) {
        // Mark single notification as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
        $stmt->execute([$notification_id, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    
} catch (Exception $e) {
    error_log("Mark notification error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
