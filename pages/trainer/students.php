<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

try {
    $pdo = getDBConnection();
    
    if ($course_id) {
        // Get students for specific course
        $stmt = $pdo->prepare("
            SELECT u.*, ce.enrolled_at, ce.progress_percentage
            FROM users u
            JOIN course_enrollments ce ON u.user_id = ce.user_id
            JOIN courses c ON ce.course_id = c.course_id
            WHERE c.course_id = ? AND c.instructor_id = ?
            ORDER BY ce.enrolled_at DESC
        ");
        $stmt->execute([$course_id, $trainer_id]);
        $students = $stmt->fetchAll();
        
        // Get course name
        $stmt = $pdo->prepare("SELECT course_name FROM courses WHERE course_id = ? AND instructor_id = ?");
        $stmt->execute([$course_id, $trainer_id]);
        $course = $stmt->fetch();
        $course_name = $course['course_name'] ?? 'Unknown';
        
    } else {
        // Get all students across all courses
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.*, COUNT(ce.course_id) as courses_count
            FROM users u
            JOIN course_enrollments ce ON u.user_id = ce.user_id
            JOIN courses c ON ce.course_id = c.course_id
            WHERE c.instructor_id = ?
            GROUP BY u.user_id
            ORDER BY u.first_name
        ");
        $stmt->execute([$trainer_id]);
        $students = $stmt->fetchAll();
        $course_name = "All Students";
    }
    
} catch (PDOException $e) {
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - TrainAI Trainer</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <?php include '../../includes/trainer-sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>👥 Students <?php echo $course_id ? '- ' . htmlspecialchars($course_name) : ''; ?></h2>
            </div>
        </div>

        <div class="dashboard-container">
            <section class="card premium-card full-width-section">
                <div class="card-header">
                    <h2>Student List (<?php echo count($students); ?>)</h2>
                </div>

                <?php if (count($students) > 0): ?>
                    <div class="students-table">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                    <th style="padding: 12px; text-align: left;">Name</th>
                                    <th style="padding: 12px; text-align: left;">Email</th>
                                    <th style="padding: 12px; text-align: center;">Enrolled Date</th>
                                    <?php if ($course_id): ?>
                                        <th style="padding: 12px; text-align: center;">Progress</th>
                                    <?php else: ?>
                                        <th style="padding: 12px; text-align: center;">Courses</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;"><strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td style="padding: 12px; text-align: center;"><?php echo date('M d, Y', strtotime($student['enrolled_at'] ?? date('Y-m-d'))); ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <?php if ($course_id): ?>
                                            📊 <?php echo $student['progress_percentage']; ?>%
                                        <?php else: ?>
                                            📚 <?php echo $student['courses_count']; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 40px;">
                        <p style="font-size: 18px;">👥 No students yet</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
