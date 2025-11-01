<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Trainer';
$last_name = $_SESSION['last_name'] ?? '';

// Default values
$stats = ['total_courses' => 0, 'total_students' => 0, 'avg_rating' => 0, 'certificates_issued' => 0];
$courses = [];
$recent_enrollments = [];

try {
    $pdo = getDBConnection();
    
    // Get trainer stats
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM courses WHERE instructor_id = ?) as total_courses,
            (SELECT COUNT(DISTINCT ce.user_id) FROM course_enrollments ce 
             JOIN courses c ON ce.course_id = c.course_id 
             WHERE c.instructor_id = ?) as total_students,
            (SELECT COALESCE(AVG(c.rating), 0) FROM courses c WHERE c.instructor_id = ?) as avg_rating,
            (SELECT COUNT(*) FROM user_certificates uc 
             JOIN courses c ON uc.course_id = c.course_id 
             WHERE c.instructor_id = ?) as certificates_issued
    ");
    $stmt->execute([$trainer_id, $trainer_id, $trainer_id, $trainer_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats = $result;
    }
    
    // Get courses with enrollment stats
    $stmt = $pdo->prepare("
        SELECT c.*, 
               (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id) as enrolled_count
        FROM courses c
        WHERE c.instructor_id = ?
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$trainer_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent enrollments
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, c.course_name, ce.enrolled_at
        FROM course_enrollments ce
        JOIN users u ON ce.user_id = u.user_id
        JOIN courses c ON ce.course_id = c.course_id
        WHERE c.instructor_id = ?
        ORDER BY ce.enrolled_at DESC
        LIMIT 8
    ");
    $stmt->execute([$trainer_id]);
    $recent_enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Trainer dashboard error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainer/" class="sidebar-logo">
                <span class="logo-icon">👨‍🏫</span>
                <span class="logo-text">
                    <strong>TrainAI</strong>
                    <small>Trainer</small>
                </span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/trainer/" class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../pages/trainer/my-courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                <span class="nav-text">My Courses</span>
            </a>
            <a href="../../pages/trainer/create-course.php" class="nav-item">
                <span class="nav-icon">➕</span>
                <span class="nav-text">Create Course</span>
            </a>
            <a href="../../pages/trainer/students.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Students</span>
            </a>
            <a href="../../pages/trainer/analytics.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Analytics</span>
            </a>
            <a href="../../pages/trainer/settings.php" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../../logout.php" class="logout-btn">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>Trainer Dashboard</h2>
            </div>
            <div class="topbar-right">
                <a href="../../pages/trainer/create-course.php" class="btn-primary">
                    <span>➕</span>
                    <span>Create Course</span>
                </a>
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($first_name . ' ' . $last_name); ?>&background=4a9d9a&color=fff&bold=true" alt="<?php echo htmlspecialchars($first_name); ?>">
                    <span><?php echo htmlspecialchars(substr($first_name, 0, 10)); ?></span>
                </div>
            </div>
        </div>

        <!-- Main Container -->
        <div class="dashboard-container">
            
            <!-- Welcome Banner -->
            <section class="welcome-banner">
                <div class="welcome-content">
                    <h2>Welcome back, <span class="trainer-name"><?php echo htmlspecialchars($first_name); ?></span>! 👨‍🏫</h2>
                    <p>Manage your courses and track student progress</p>
                </div>
            </section>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                    <div class="stat-icon stat-icon-teal">📚</div>
                    <div class="stat-content">
                        <p class="stat-label">Courses Created</p>
                        <h3 class="stat-number"><?php echo (int)($stats['total_courses'] ?? 0); ?></h3>
                    </div>
                </div>
                
                <div class="stat-box" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                    <div class="stat-icon stat-icon-green">👥</div>
                    <div class="stat-content">
                        <p class="stat-label">Total Students</p>
                        <h3 class="stat-number"><?php echo (int)($stats['total_students'] ?? 0); ?></h3>
                    </div>
                </div>
                
                <div class="stat-box" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                    <div class="stat-icon stat-icon-gold">⭐</div>
                    <div class="stat-content">
                        <p class="stat-label">Average Rating</p>
                        <h3 class="stat-number"><?php echo number_format((float)($stats['avg_rating'] ?? 0), 1); ?></h3>
                    </div>
                </div>
                
                <div class="stat-box" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
                    <div class="stat-icon stat-icon-blue">🎓</div>
                    <div class="stat-content">
                        <p class="stat-label">Certificates Issued</p>
                        <h3 class="stat-number"><?php echo (int)($stats['certificates_issued'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                
                <!-- My Courses Card -->
                <div class="card premium-card">
                    <div class="card-header">
                        <h2>📚 My Courses</h2>
                        <a href="../../pages/trainer/my-courses.php" class="view-all">See all →</a>
                    </div>
                    <div class="card-content">
                        <?php if (count($courses) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <?php foreach (array_slice($courses, 0, 4) as $course): ?>
                                    <div style="padding: 16px; background: white; border-radius: 8px; border-left: 4px solid #4a9d9a; border: 1px solid #e5e7eb; border-left-width: 4px;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 12px;">
                                            <div style="flex: 1;">
                                                <h4 style="font-size: 14px; font-weight: 600; color: #1f2937; margin-bottom: 6px;">
                                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                                </h4>
                                                <div style="display: flex; gap: 12px; font-size: 13px; color: #6b7280; flex-wrap: wrap;">
                                                    <span>👥 <?php echo $course['enrolled_count']; ?> students</span>
                                                    <span>⭐ <?php echo number_format($course['rating'], 1); ?></span>
                                                    <span>📂 <?php echo htmlspecialchars($course['category']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- ACTION BUTTONS -->
                                        <div style="display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
                                            <a href="../../pages/trainer/edit-course.php?id=<?php echo $course['course_id']; ?>" 
                                               class="btn-small" style="font-size: 12px; padding: 6px 12px; text-decoration: none;">✏️ Edit</a>
                                            
                                            <a href="../../pages/trainer/add-module.php?course_id=<?php echo $course['course_id']; ?>" 
                                               class="btn-small" style="font-size: 12px; padding: 6px 12px; text-decoration: none; background: #10b981;">📚 Modules</a>
                                            
                                            <a href="../../pages/trainer/create-exam.php?course_id=<?php echo $course['course_id']; ?>" 
                                               class="btn-small" style="font-size: 12px; padding: 6px 12px; text-decoration: none; background: #f59e0b;">❓ Exam</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: #6b7280;">
                                <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                                <p style="font-size: 14px; margin-bottom: 16px;">No courses yet</p>
                                <a href="../../pages/trainer/create-course.php" class="btn-primary" style="display: inline-block;">Create Your First Course</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Enrollments Card -->
                <div class="card premium-card">
                    <div class="card-header">
                        <h2>🎉 Recent Enrollments</h2>
                        <a href="../../pages/trainer/students.php" class="view-all">View all →</a>
                    </div>
                    <div class="card-content">
                        <?php if (count($recent_enrollments) > 0): ?>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <?php foreach ($recent_enrollments as $enrollment): ?>
                                    <div style="padding: 12px; background: white; border-radius: 8px; border-left: 4px solid #4a9d9a; border: 1px solid #e5e7eb; border-left-width: 4px;">
                                        <div style="font-size: 13px;">
                                            <strong style="color: #1f2937;">
                                                <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                            </strong>
                                            <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">
                                                Enrolled in <strong><?php echo htmlspecialchars($enrollment['course_name']); ?></strong>
                                                <br>
                                                <?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: #6b7280;">
                                <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                                <p style="font-size: 14px;">No recent enrollments</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- All Courses Table -->
            <div class="card premium-card">
                <div class="card-header">
                    <h2>📊 Courses Overview</h2>
                    <a href="../../pages/trainer/my-courses.php" class="view-all">Manage all →</a>
                </div>
                <div class="card-content">
                    <?php if (count($courses) > 0): ?>
                        <div class="table-responsive">
                            <table class="courses-table">
                                <thead>
                                    <tr>
                                        <th>Course Name</th>
                                        <th>Category</th>
                                        <th>Students</th>
                                        <th>Rating</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($course['course_name']); ?></strong>
                                        </td>
                                        <td>
                                            <span style="background: #d0f0f0; color: #4a9d9a; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                                <?php echo htmlspecialchars($course['category']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $course['enrolled_count']; ?></td>
                                        <td>
                                            <strong style="color: #fbbf24;">⭐ <?php echo number_format($course['rating'], 1); ?></strong>
                                        </td>
                                        <td><?php echo $course['duration_hours'] ?? 0; ?>h</td>
                                        <td style="display: flex; gap: 8px;">
                                            <a href="../../pages/trainer/edit-course.php?id=<?php echo $course['course_id']; ?>" class="btn-small" style="font-size: 12px; padding: 6px 12px; text-decoration: none;">Edit</a>
                                            
                                            <a href="../../pages/trainer/add-module.php?course_id=<?php echo $course['course_id']; ?>" class="btn-small" style="font-size: 12px; padding: 6px 12px; text-decoration: none; background: #10b981;">Modules</a>
                                            
                                            <a href="../../pages/trainer/create-exam.php?course_id=<?php echo $course['course_id']; ?>" class="btn-small" style="font-size: 12px; padding: 6px 12px; text-decoration: none; background: #f59e0b;">Exam</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                            <h3>No Courses Yet</h3>
                            <p>Create your first course to get started</p>
                            <a href="../../pages/trainer/create-course.php" class="btn-primary" style="display: inline-block; margin-top: 16px;">Create Course</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
