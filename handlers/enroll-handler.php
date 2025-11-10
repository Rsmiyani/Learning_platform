<?php
require_once '../config/database.php';
initSession();

header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$course_id = $data['course_id'] ?? null;

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if already enrolled
    $stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Already enrolled in this course']);
        exit;
    }
    
    // Add enrollment with active status
    $stmt = $pdo->prepare("
        INSERT INTO course_enrollments (user_id, course_id, progress_percentage, status, enrolled_at)
        VALUES (?, ?, 0, 'active', NOW())
    ");
    $stmt->execute([$user_id, $course_id]);
    
    // Ensure user_points record exists (but don't add points on enrollment)
    $stmt = $pdo->prepare("
        INSERT INTO user_points (user_id, total_points, level)
        VALUES (?, 0, 1)
        ON DUPLICATE KEY UPDATE total_points = total_points
    ");
    $stmt->execute([$user_id]);
    
    // Get course name for response
    $stmt = $pdo->prepare("SELECT course_name FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully enrolled in ' . ($course['course_name'] ?? 'course') . '!',
        'course_name' => $course['course_name'] ?? 'Course'
    ]);
    
} catch (Exception $e) {
    error_log("Enrollment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
