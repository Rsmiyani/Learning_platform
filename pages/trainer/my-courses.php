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
    
    // Get all trainer's courses
    $stmt = $pdo->prepare("
        SELECT c.*,
               (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id) as enrolled_count,
               (SELECT COUNT(*) FROM user_certificates uc WHERE uc.course_id = c.course_id) as certificates_count
        FROM courses c
        WHERE c.instructor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$trainer_id]);
    $courses = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $courses = [];
    $error = "Error loading courses";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - TrainAI Trainer</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <?php include '../../includes/trainer-sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>My Courses</h2>
            </div>
            <div class="topbar-right">
                <a href="../../pages/trainer/create-course.php" class="btn-primary">+ Create New Course</a>
            </div>
        </div>

        <div class="dashboard-container">
            <section class="card premium-card full-width-section">
                <div class="card-header">
                    <h2>📚 All Courses (<?php echo count($courses); ?>)</h2>
                </div>

                <?php if (count($courses) > 0): ?>
                    <div class="courses-table">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                                    <th style="padding: 12px; text-align: left;">Course Name</th>
                                    <th style="padding: 12px; text-align: left;">Category</th>
                                    <th style="padding: 12px; text-align: center;">Enrolled</th>
                                    <th style="padding: 12px; text-align: center;">Certificates</th>
                                    <th style="padding: 12px; text-align: center;">Rating</th>
                                    <th style="padding: 12px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;"><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($course['category']); ?></td>
                                    <td style="padding: 12px; text-align: center;">👥 <?php echo $course['enrolled_count']; ?></td>
                                    <td style="padding: 12px; text-align: center;">🎓 <?php echo $course['certificates_count']; ?></td>
                                    <td style="padding: 12px; text-align: center;">⭐ <?php echo $course['rating']; ?></td>
                                    <td style="padding: 12px; text-align: center;">
                                        <a href="../../pages/trainer/edit-course.php?id=<?php echo $course['course_id']; ?>" class="btn-small" style="margin-right: 5px;">Edit</a>
                                        <a href="../../pages/trainer/students.php?course_id=<?php echo $course['course_id']; ?>" class="btn-small">Students</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 40px;">
                        <p style="font-size: 18px; margin-bottom: 20px;">📚 No courses yet. Create your first course!</p>
                        <a href="../../pages/trainer/create-course.php" class="btn-primary">Create Course</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
