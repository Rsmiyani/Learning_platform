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
    <style>
        .courses-table {
            overflow-x: auto;
        }
        
        .courses-table table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .courses-table thead tr {
            background: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .courses-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }
        
        .courses-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        
        .courses-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .courses-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .btn-small {
            padding: 6px 12px;
            background: #6B5B95;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-small:hover {
            background: #4a3d6f;
            transform: translateY(-1px);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px dashed #e5e7eb;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }
        
        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 24px;
            display: block;
            line-height: 1;
        }
        
        .empty-state h3 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .empty-state p {
            color: #6b7280;
            margin-bottom: 32px;
            font-size: 16px;
            line-height: 1.6;
            max-width: 500px;
        }
        
        .empty-state .btn-primary {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #6B5B95 0%, #4a3d6f 100%);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(107, 91, 149, 0.3);
            border: none;
        }
        
        .empty-state .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(107, 91, 149, 0.4);
        }
        
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
        }
        
        .full-width-section {
            width: 100%;
            margin-bottom: 0;
        }
    </style>
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
                        <table>
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Category</th>
                                    <th style="text-align: center;">Enrolled</th>
                                    <th style="text-align: center;">Certificates</th>
                                    <th style="text-align: center;">Rating</th>
                                    <th style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($course['category'] ?? 'N/A'); ?></td>
                                    <td style="text-align: center;">👥 <?php echo $course['enrolled_count']; ?></td>
                                    <td style="text-align: center;">🎓 <?php echo $course['certificates_count']; ?></td>
                                    <td style="text-align: center;">⭐ <?php echo number_format($course['rating'] ?? 0, 1); ?></td>
                                    <td style="text-align: center;">
                                        <a href="../../pages/trainer/edit-course.php?id=<?php echo $course['course_id']; ?>" class="btn-small" style="margin-right: 5px;">Edit</a>
                                        <a href="../../pages/trainer/students.php?course_id=<?php echo $course['course_id']; ?>" class="btn-small">Students</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <h3>No courses yet</h3>
                        <p>Create your first course and start teaching!</p>
                        <a href="../../pages/trainer/create-course.php" class="btn-primary">Create Course</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
