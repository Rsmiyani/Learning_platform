<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get all courses with instructor and enrollment info
    $stmt = $pdo->prepare("
        SELECT c.*, 
               CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
               (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id) as total_enrollments,
               (SELECT COUNT(*) FROM user_certificates WHERE course_id = c.course_id) as total_certificates
        FROM courses c
        LEFT JOIN users u ON c.instructor_id = u.user_id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $all_courses = $stmt->fetchAll();
    
    // Get statistics
    $total_courses = count($all_courses);
    $total_enrollments = array_sum(array_column($all_courses, 'total_enrollments'));
    $total_certificates = array_sum(array_column($all_courses, 'total_certificates'));
    
} catch (PDOException $e) {
    error_log("Courses page error: " . $e->getMessage());
    $all_courses = [];
    $total_courses = $total_enrollments = $total_certificates = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .courses-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }
        
        .course-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .course-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .course-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .course-table tr:hover {
            background: #f9fafb;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            color: white;
        }
        
        .btn-view { background: #3b82f6; }
        .btn-edit { background: #8b5cf6; }
        .btn-delete { background: #dc2626; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/admin/" class="sidebar-logo">
                <span class="logo-icon">👑</span>
                <span class="logo-text">TrainAI Admin</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/admin/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../dashboard/admin/users.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Manage Users</span>
            </a>
            <a href="../../dashboard/admin/courses.php" class="nav-item active">
                <span class="nav-icon">📚</span>
                <span class="nav-text">Manage Courses</span>
            </a>
            <a href="../../dashboard/admin/enrollments.php" class="nav-item">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Enrollments</span>
            </a>
            <a href="../../dashboard/admin/certificates.php" class="nav-item">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Certificates</span>
            </a>
            <a href="../../dashboard/admin/reports.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Reports</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../../logout.php" class="logout-btn">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>📚 Manage Courses</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo $total_courses; ?></div>
                    <div class="label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $total_enrollments; ?></div>
                    <div class="label">Total Enrollments</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $total_certificates; ?></div>
                    <div class="label">Certificates Issued</div>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="courses-section">
                <h2 style="margin: 0 0 20px 0;">All Courses</h2>
                
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="🔍 Search courses..." onkeyup="filterTable()">
                </div>

                <div style="overflow-x: auto;">
                    <table class="course-table" id="coursesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Course Name</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Instructor</th>
                                <th>Duration</th>
                                <th>Rating</th>
                                <th>Enrollments</th>
                                <th>Certificates</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_courses as $course): ?>
                            <tr>
                                <td><?php echo $course['course_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['category'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $course['duration_hours']; ?>h</td>
                                <td>⭐ <?php echo $course['rating']; ?> (<?php echo $course['total_ratings']; ?>)</td>
                                <td><?php echo $course['total_enrollments']; ?></td>
                                <td><?php echo $course['total_certificates']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                <td>
                                    <button class="action-btn btn-view" onclick="viewCourse(<?php echo $course['course_id']; ?>)">
                                        👁️ View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
    <script>
        function filterTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('coursesTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }

        function viewCourse(courseId) {
            window.location.href = `../../pages/trainee/course-modules.php?course_id=${courseId}`;
        }
    </script>
</body>
</html>
