<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
initSession();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$course_id = $data['course_id'] ?? null;
$rating_value = $data['rating'] ?? null;
$review_text = $data['review'] ?? '';

if (!$course_id || !$rating_value || $rating_value < 1 || $rating_value > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating data']);
    exit;
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("
        INSERT INTO course_ratings (user_id, course_id, rating_value, review_text, created_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        rating_value = VALUES(rating_value),
        review_text = VALUES(review_text),
        updated_at = NOW()
    ");
    $stmt->execute([$user_id, $course_id, $rating_value, $review_text]);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_ratings, AVG(rating_value) as avg_rating
        FROM course_ratings
        WHERE course_id = ?
    ");
    $stmt->execute([$course_id]);
    $rating_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        UPDATE courses
        SET rating = ?, total_ratings = ?
        WHERE course_id = ?
    ");
    $stmt->execute([
        round($rating_stats['avg_rating'], 1),
        $rating_stats['total_ratings'],
        $course_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => '✅ Rating submitted successfully! ⭐',
        'new_rating' => round($rating_stats['avg_rating'], 1),
        'total_ratings' => $rating_stats['total_ratings']
    ]);

} catch (Exception $e) {
    error_log("Rating submission error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
