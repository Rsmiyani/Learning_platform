<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$enrollment_id = $_GET['enrollment_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$enrollment_id || !$user_id) {
    $_SESSION['error'] = "Invalid parameters";
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get enrollment info before deleting
    $stmt = $pdo->prepare("
        SELECT ce.*, c.course_name, u.first_name, u.last_name
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.course_id
        JOIN users u ON ce.user_id = u.user_id
        WHERE ce.enrollment_id = ?
    ");
    $stmt->execute([$enrollment_id]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        $_SESSION['error'] = "Enrollment not found";
        header('Location: user-enrollments.php?user_id=' . $user_id);
        exit;
    }
    
    // Delete the enrollment
    $stmt = $pdo->prepare("DELETE FROM course_enrollments WHERE enrollment_id = ?");
    $stmt->execute([$enrollment_id]);
    
    $_SESSION['success'] = "Successfully unenrolled {$enrollment['first_name']} {$enrollment['last_name']} from {$enrollment['course_name']}";
    
} catch (PDOException $e) {
    error_log("Unenroll error: " . $e->getMessage());
    $_SESSION['error'] = "Error unenrolling user: " . $e->getMessage();
}

header('Location: user-enrollments.php?user_id=' . $user_id);
exit;
?>
