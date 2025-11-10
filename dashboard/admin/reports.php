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
    
    // Platform Overview
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'trainee'");
    $total_students = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'trainer'");
    $total_trainers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
    $total_courses = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM course_enrollments");
    $total_enrollments = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user_certificates");
    $total_certificates = $stmt->fetch()['total'];
    
    // Top Courses by Enrollment
    $stmt = $pdo->prepare("
        SELECT c.course_name, c.course_code, COUNT(ce.enrollment_id) as enrollment_count
        FROM courses c
        LEFT JOIN course_enrollments ce ON c.course_id = ce.course_id
        GROUP BY c.course_id
        ORDER BY enrollment_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $top_courses = $stmt->fetchAll();
    
    // Top Students by Points
    $stmt = $pdo->prepare("
        SELECT CONCAT(u.first_name, ' ', u.last_name) as name, 
               COALESCE(up.total_points, 0) as points,
               COALESCE(up.level, 1) as level
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        WHERE u.role = 'trainee'
        ORDER BY points DESC
        LIMIT 10
    ");
    $stmt->execute();
    $top_students = $stmt->fetchAll();
    
    // Recent Activity (Last 30 days)
    $stmt = $pdo->prepare("
        SELECT DATE(enrolled_at) as date, COUNT(*) as count
        FROM course_enrollments
        WHERE enrolled_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(enrolled_at)
        ORDER BY date DESC
    ");
    $stmt->execute();
    $enrollment_trend = $stmt->fetchAll();
    
    // Certificates Issued (Last 30 days)
    $stmt = $pdo->prepare("
        SELECT DATE(issued_date) as date, COUNT(*) as count
        FROM user_certificates
        WHERE issued_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(issued_date)
        ORDER BY date DESC
    ");
    $stmt->execute();
    $certificate_trend = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Reports page error: " . $e->getMessage());
    $total_students = $total_trainers = $total_courses = $total_enrollments = $total_certificates = 0;
    $top_courses = $top_students = $enrollment_trend = $certificate_trend = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #667eea;
        }
        
        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .report-card h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 18px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            font-weight: 600;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
        }
        
        .stat-item:hover {
            background: #f9fafb;
            padding-left: 10px;
            padding-right: 10px;
            margin-left: -10px;
            margin-right: -10px;
            border-radius: 6px;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .stat-value {
            font-weight: 700;
            color: #667eea;
            font-size: 15px;
        }
        
        .overview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .overview-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .overview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .overview-card .number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .overview-card .label {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 500;
        }
        
        .trend-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .trend-item {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        
        .export-btn {
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .export-btn:hover {
            background: #059669;
        }
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
            <a href="../../dashboard/admin/courses.php" class="nav-item">
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
            <a href="../../dashboard/admin/reports.php" class="nav-item active">
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
                <h2>📊 Reports & Analytics</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
                <button class="export-btn" onclick="exportReport('csv')" style="background: #10b981;">
                    📊 Export as CSV
                </button>
                <button class="export-btn" onclick="exportReport('excel')" style="background: #059669;">
                    📈 Export as Excel
                </button>
                <button class="export-btn" onclick="window.print()" style="background: #6b7280;">
                    🖨️ Print Report
                </button>
            </div>
            
            <script>
                function exportReport(format) {
                    window.location.href = 'export-report.php?format=' + format;
                }
            </script>

            <!-- Platform Overview -->
            <div class="overview-stats">
                <div class="overview-card">
                    <div class="number"><?php echo $total_students; ?></div>
                    <div class="label">Students</div>
                </div>
                <div class="overview-card">
                    <div class="number"><?php echo $total_trainers; ?></div>
                    <div class="label">Trainers</div>
                </div>
                <div class="overview-card">
                    <div class="number"><?php echo $total_courses; ?></div>
                    <div class="label">Courses</div>
                </div>
                <div class="overview-card">
                    <div class="number"><?php echo $total_enrollments; ?></div>
                    <div class="label">Enrollments</div>
                </div>
                <div class="overview-card">
                    <div class="number"><?php echo $total_certificates; ?></div>
                    <div class="label">Certificates</div>
                </div>
            </div>

            <!-- Detailed Reports -->
            <div class="reports-grid">
                <!-- Top Courses -->
                <div class="report-card">
                    <h3>📚 Top Courses by Enrollment</h3>
                    <div class="trend-list">
                        <?php foreach ($top_courses as $course): ?>
                            <div class="stat-item">
                                <span class="stat-label"><?php echo htmlspecialchars($course['course_name']); ?></span>
                                <span class="stat-value"><?php echo $course['enrollment_count']; ?> students</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Top Students -->
                <div class="report-card">
                    <h3>🏆 Top Students by Points</h3>
                    <div class="trend-list">
                        <?php foreach ($top_students as $student): ?>
                            <div class="stat-item">
                                <span class="stat-label"><?php echo htmlspecialchars($student['name']); ?></span>
                                <span class="stat-value"><?php echo $student['points']; ?> pts (L<?php echo $student['level']; ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Enrollment Trend -->
                <div class="report-card">
                    <h3>📈 Enrollment Trend (Last 30 Days)</h3>
                    <div class="trend-list">
                        <?php foreach ($enrollment_trend as $trend): ?>
                            <div class="trend-item">
                                <span><?php echo date('M d, Y', strtotime($trend['date'])); ?></span>
                                <span><strong><?php echo $trend['count']; ?></strong> enrollments</span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($enrollment_trend)): ?>
                            <p style="color: #6b7280; text-align: center; padding: 20px;">No enrollments in last 30 days</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Certificate Trend -->
                <div class="report-card">
                    <h3>🎓 Certificates Issued (Last 30 Days)</h3>
                    <div class="trend-list">
                        <?php foreach ($certificate_trend as $trend): ?>
                            <div class="trend-item">
                                <span><?php echo date('M d, Y', strtotime($trend['date'])); ?></span>
                                <span><strong><?php echo $trend['count']; ?></strong> certificates</span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($certificate_trend)): ?>
                            <p style="color: #6b7280; text-align: center; padding: 20px;">No certificates issued in last 30 days</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="report-card">
                <h3>📊 Platform Summary</h3>
                <div class="stat-item">
                    <span class="stat-label">Average Enrollments per Course</span>
                    <span class="stat-value"><?php echo $total_courses > 0 ? round($total_enrollments / $total_courses, 1) : 0; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Average Enrollments per Student</span>
                    <span class="stat-value"><?php echo $total_students > 0 ? round($total_enrollments / $total_students, 1) : 0; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Certificate Completion Rate</span>
                    <span class="stat-value"><?php echo $total_enrollments > 0 ? round(($total_certificates / $total_enrollments) * 100, 1) : 0; ?>%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Courses per Trainer</span>
                    <span class="stat-value"><?php echo $total_trainers > 0 ? round($total_courses / $total_trainers, 1) : 0; ?></span>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
