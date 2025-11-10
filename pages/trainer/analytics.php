<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Get analytics data
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM courses WHERE instructor_id = ?) as total_courses,
            (SELECT COUNT(DISTINCT ce.user_id) FROM course_enrollments ce 
             JOIN courses c ON ce.course_id = c.course_id 
             WHERE c.instructor_id = ?) as total_students,
            (SELECT COALESCE(AVG(c.rating), 0) FROM courses c WHERE c.instructor_id = ?) as avg_rating,
            (SELECT COUNT(*) FROM user_certificates uc 
             JOIN courses c ON uc.course_id = c.course_id 
             WHERE c.instructor_id = ?) as certificates_issued,
            (SELECT COUNT(*) FROM course_enrollments ce
             JOIN courses c ON ce.course_id = c.course_id
             WHERE c.instructor_id = ?) as total_enrollments
    ");
    $stmt->execute([$trainer_id, $trainer_id, $trainer_id, $trainer_id, $trainer_id]);
    $stats = $stmt->fetch();
    
    // Get courses with enrollment stats
    $stmt = $pdo->prepare("
        SELECT c.course_name, 
               (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id) as enrollments,
               (SELECT COUNT(*) FROM user_certificates WHERE course_id = c.course_id) as certificates,
               c.rating
        FROM courses c
        WHERE c.instructor_id = ?
        ORDER BY enrollments DESC
        LIMIT 10
    ");
    $stmt->execute([$trainer_id]);
    $course_stats = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $stats = $course_stats = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - TrainAI Trainer</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <?php include '../../includes/trainer-sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>📊 Analytics</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <!-- Stats Cards -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon stat-icon-blue">📚</div>
                    <div class="stat-content">
                        <p class="stat-label">Courses</p>
                        <h3 class="stat-number"><?php echo $stats['total_courses'] ?? 0; ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon stat-icon-green">👥</div>
                    <div class="stat-content">
                        <p class="stat-label">Students</p>
                        <h3 class="stat-number"><?php echo $stats['total_students'] ?? 0; ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon stat-icon-purple">⭐</div>
                    <div class="stat-content">
                        <p class="stat-label">Avg Rating</p>
                        <h3 class="stat-number"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon stat-icon-orange">🎓</div>
                    <div class="stat-content">
                        <p class="stat-label">Certificates</p>
                        <h3 class="stat-number"><?php echo $stats['certificates_issued'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>

            <!-- Course Stats Table -->
            <section class="card premium-card full-width-section">
                <div class="card-header">
                    <h2>📊 Course Performance</h2>
                </div>

                <?php if (count($course_stats) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                <th style="padding: 12px; text-align: left;">Course</th>
                                <th style="padding: 12px; text-align: center;">Enrollments</th>
                                <th style="padding: 12px; text-align: center;">Certificates</th>
                                <th style="padding: 12px; text-align: center;">Rating</th>
                                <th style="padding: 12px; text-align: center;">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($course_stats as $course): 
                                $completion_rate = $course['enrollments'] > 0 ? round(($course['certificates'] / $course['enrollments']) * 100) : 0;
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></td>
                                <td style="padding: 12px; text-align: center;"><?php echo $course['enrollments']; ?></td>
                                <td style="padding: 12px; text-align: center;"><?php echo $course['certificates']; ?></td>
                                <td style="padding: 12px; text-align: center;">⭐ <?php echo $course['rating']; ?></td>
                                <td style="padding: 12px; text-align: center;">📊 <?php echo $completion_rate; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 40px;">
                        <p>No data yet</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
