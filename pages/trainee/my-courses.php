<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if ($_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDBConnection();

// Get enrolled courses for this trainee with instructor name
$stmt = $pdo->prepare("
    SELECT c.*, ce.status, ce.progress_percentage, ce.enrolled_at,
           CONCAT(u.first_name, ' ', u.last_name) as instructor_name
    FROM courses c
    JOIN course_enrollments ce ON c.course_id = ce.course_id
    LEFT JOIN users u ON c.instructor_id = u.user_id
    WHERE ce.user_id = ?
    ORDER BY ce.enrolled_at DESC
");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();

// Calculate progress for each course based on exam completion
foreach ($courses as &$course) {
    if ($course['status'] === 'completed') {
        $course['progress_percentage'] = 100;
    } else {
        // Check if exam has been attempted
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM exam_results WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$user_id, $course['course_id']]);
        $exam_attempted = $stmt->fetch()['cnt'] > 0;
        
        if ($exam_attempted) {
            $course['progress_percentage'] = 75; // Exam attempted but not passed
        } else {
            // Check if any modules exist
            $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM course_modules WHERE course_id = ?");
            $stmt->execute([$course['course_id']]);
            $has_modules = $stmt->fetch()['cnt'] > 0;
            
            if ($has_modules) {
                $course['progress_percentage'] = 25; // Course started (has modules)
            } else {
                $course['progress_percentage'] = 0; // Not started
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .learning-journey {
            background: linear-gradient(135deg, #4a9d9a 0%, #2d7a77 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .learning-journey h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .learning-journey p {
            margin: 0;
            opacity: 0.9;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .course-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-top: 4px solid #4a9d9a;
        }

        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .course-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .course-code {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .course-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .course-body {
            padding: 15px 20px;
        }

        .course-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
        }

        .meta-label {
            font-weight: 600;
            color: #4a9d9a;
        }

        .progress-section {
            margin-bottom: 15px;
        }

        .progress-label {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4a9d9a 0%, #2d7a77 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        .course-footer {
            display: flex;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            flex: 1;
            padding: 10px 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4a9d9a 0%, #2d7a77 100%);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .empty-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .empty-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .empty-text {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .btn-explore {
            background: linear-gradient(135deg, #4a9d9a 0%, #2d7a77 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            display: inline-block;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-explore:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .rating {
            color: #f59e0b;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 20px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: #6b7280;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background: #f3f4f6;
            color: #4a9d9a;
            border-left-color: #4a9d9a;
        }

        .nav-item.active {
            background: #eef7f7;
            color: #4a9d9a;
            border-left-color: #4a9d9a;
            font-weight: 600;
        }

        .nav-icon {
            font-size: 18px;
        }

        .nav-label {
            font-size: 14px;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 20px;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }

            .course-footer {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include '../../includes/trainee-sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h2>📚 My Courses</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <?php if (count($courses) > 0): ?>
                <div class="learning-journey">
                    <h2>Your Learning Journey</h2>
                    <p>Continue your enrolled courses and track progress</p>
                </div>

                <h3 style="color: #1f2937; margin-bottom: 20px;">Enrolled Courses (<?php echo count($courses); ?>)</h3>

                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-code">Code: <?php echo htmlspecialchars($course['course_code']); ?></div>
                                <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            </div>

                            <div class="course-body">
                                <div class="course-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">👤 Instructor:</span>
                                        <span><?php echo htmlspecialchars($course['instructor_name'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">📂 Category:</span>
                                        <span><?php echo htmlspecialchars($course['category'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">⏱️ Duration:</span>
                                        <span><?php echo $course['duration_hours'] ?? 'N/A'; ?> hours</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">⭐ Rating:</span>
                                        <span class="rating"><?php echo $course['rating'] ?? '0.0'; ?>/5.0</span>
                                    </div>
                                </div>

                                <div class="progress-section">
                                    <div class="progress-label">
                                        <span>Progress</span>
                                        <span><?php echo round($course['progress_percentage']); ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo round($course['progress_percentage']); ?>%;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="course-footer">
                                <a href="course-modules.php?course_id=<?php echo $course['course_id']; ?>" class="btn btn-primary">Continue Learning →</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <div class="empty-title">You haven't enrolled in any courses yet</div>
                    <p class="empty-text">Explore our courses and start your learning journey today!</p>
                    <a href="../courses/" class="btn-explore">Explore Courses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
