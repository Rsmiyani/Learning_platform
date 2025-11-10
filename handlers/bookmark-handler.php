<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

// CORRECT PATH: handlers/ -> ../config/
require_once '../config/database.php';
initSession();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$course_id = $data['course_id'] ?? null;
$action = $data['action'] ?? 'toggle';

if (!$course_id || !is_numeric($course_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit;
}

try {
    $pdo = getDBConnection();

    if ($action === 'add') {
        $stmt = $pdo->prepare("
            INSERT INTO user_bookmarks (user_id, course_id, bookmarked_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE bookmarked_at = NOW()
        ");
        $stmt->execute([$user_id, $course_id]);
        echo json_encode(['success' => true, 'message' => 'Course bookmarked!', 'action' => 'added']);
        
    } else if ($action === 'remove') {
        $stmt = $pdo->prepare("
            DELETE FROM user_bookmarks
            WHERE user_id = ? AND course_id = ?
        ");
        $stmt->execute([$user_id, $course_id]);
        echo json_encode(['success' => true, 'message' => 'Bookmark removed!', 'action' => 'removed']);
        
    } else if ($action === 'toggle') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as is_bookmarked
            FROM user_bookmarks
            WHERE user_id = ? AND course_id = ?
        ");
        $stmt->execute([$user_id, $course_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['is_bookmarked'] > 0) {
            $stmt = $pdo->prepare("
                DELETE FROM user_bookmarks
                WHERE user_id = ? AND course_id = ?
            ");
            $stmt->execute([$user_id, $course_id]);
            echo json_encode(['success' => true, 'message' => 'Bookmark removed!', 'action' => 'removed']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO user_bookmarks (user_id, course_id, bookmarked_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$user_id, $course_id]);
            echo json_encode(['success' => true, 'message' => 'Course bookmarked!', 'action' => 'added']);
        }
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit(1);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error']);
    exit(1);
}
?>
