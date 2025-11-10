<?php
require_once '../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$request_id = $_GET['request_id'] ?? null;
$action = $_GET['action'] ?? ''; // 'approve' or 'reject'
$admin_note = trim($_POST['admin_note'] ?? '');

if (!$request_id || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = "Invalid request parameters.";
    header('Location: ../dashboard/admin/trainer-requests.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get the request details
    $stmt = $pdo->prepare("
        SELECT tr.*, u.first_name, u.last_name, u.email, u.role
        FROM trainer_requests tr
        JOIN users u ON tr.user_id = u.user_id
        WHERE tr.request_id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch();
    
    if (!$request) {
        $_SESSION['error'] = "Trainer request not found.";
        header('Location: ../dashboard/admin/trainer-requests.php');
        exit;
    }
    
    // Check if already processed
    if ($request['status'] !== 'pending') {
        $_SESSION['error'] = "This request has already been processed.";
        header('Location: ../dashboard/admin/trainer-requests.php');
        exit;
    }
    
    // Update request status
    $new_status = $action === 'approve' ? 'approved' : 'rejected';
    $stmt = $pdo->prepare("
        UPDATE trainer_requests 
        SET status = ?, 
            reviewed_by = ?, 
            reviewed_at = NOW(),
            admin_note = ?
        WHERE request_id = ?
    ");
    $stmt->execute([$new_status, $admin_id, $admin_note, $request_id]);
    
    // If approved, update user role to trainer
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET role = 'trainer' WHERE user_id = ?");
        $stmt->execute([$request['user_id']]);
        
        // Create notification for the user
        $notification_message = "🎉 Congratulations! Your trainer request has been approved. You can now access the trainer dashboard.";
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, is_read, created_at)
            VALUES (?, ?, 0, NOW())
        ");
        $stmt->execute([$request['user_id'], $notification_message]);
        
        $_SESSION['success'] = "Trainer request approved successfully. User role has been updated to trainer.";
    } else {
        // Create notification for rejection
        $notification_message = "Your trainer request has been rejected. " . ($admin_note ? "Note: " . $admin_note : "");
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, message, is_read, created_at)
            VALUES (?, ?, 0, NOW())
        ");
        $stmt->execute([$request['user_id'], $notification_message]);
        
        $_SESSION['success'] = "Trainer request rejected.";
    }
    
    header('Location: ../dashboard/admin/trainer-requests.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Process trainer request error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing the request.";
    header('Location: ../dashboard/admin/trainer-requests.php');
    exit;
}
?>

