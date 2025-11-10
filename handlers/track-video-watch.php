<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
initSession();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$lesson_id = $data['lesson_id'] ?? null;
$course_id = $data['course_id'] ?? null;
$watch_duration = $data['watch_duration'] ?? 0; // Duration in seconds

if (!$lesson_id || !$course_id || $watch_duration <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Convert seconds to hours (rounded to 2 decimals)
    $hours = round($watch_duration / 3600, 2);
    
    // Insert or update video watch log
    $stmt = $pdo->prepare("
        INSERT INTO video_watch_logs 
        (user_id, lesson_id, course_id, watch_duration, watch_date)
        VALUES (?, ?, ?, ?, CURDATE())
        ON DUPLICATE KEY UPDATE 
            watch_duration = watch_duration + VALUES(watch_duration)
    ");
    $stmt->execute([$user_id, $lesson_id, $course_id, $hours]);
    
    // Also update study_logs for weekly activity chart
    $stmt = $pdo->prepare("
        INSERT INTO study_logs 
        (user_id, study_date, hours_studied, courses_studied, activities)
        VALUES (?, CURDATE(), ?, 'Video Learning', 'Watched lesson videos')
        ON DUPLICATE KEY UPDATE 
            hours_studied = hours_studied + VALUES(hours_studied)
    ");
    $stmt->execute([$user_id, $hours]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Watch time tracked',
        'hours' => $hours
    ]);

} catch (Exception $e) {
    error_log("Video tracking error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
