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
    
    // Get all enrollments
    $stmt = $pdo->prepare("
        SELECT ce.*, 
               c.course_name, c.course_code,
               CONCAT(u.first_name, ' ', u.last_name) as student_name,
               u.email as student_email,
               CONCAT(t.first_name, ' ', t.last_name) as instructor_name
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.course_id
        JOIN users u ON ce.user_id = u.user_id
        LEFT JOIN users t ON c.instructor_id = t.user_id
        ORDER BY ce.enrolled_at DESC
    ");
    $stmt->execute();
    $all_enrollments = $stmt->fetchAll();
    
    // Get statistics
    $total_enrollments = count($all_enrollments);
    $active_enrollments = count(array_filter($all_enrollments, fn($e) => $e['status'] === 'active'));
    $completed_enrollments = count(array_filter($all_enrollments, fn($e) => $e['status'] === 'completed'));
    
} catch (PDOException $e) {
    error_log("Enrollments page error: " . $e->getMessage());
    $all_enrollments = [];
    $total_enrollments = $active_enrollments = $completed_enrollments = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollments - Admin</title>
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
        
        .enrollments-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .search-filter {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-filter input,
        .search-filter select {
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
        }
        
        .search-filter input {
            flex: 1;
            min-width: 250px;
        }
        
        .enrollment-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .enrollment-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .enrollment-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .enrollment-table tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active { background: #d1fae5; color: #059669; }
        .status-completed { background: #dbeafe; color: #2563eb; }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            background: #dc2626;
            color: white;
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
            <a href="../../dashboard/admin/enrollments.php" class="nav-item active">
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
                <h2>📝 Enrollments</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #d1fae5; color: #059669; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo $total_enrollments; ?></div>
                    <div class="label">Total Enrollments</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $active_enrollments; ?></div>
                    <div class="label">Active</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $completed_enrollments; ?></div>
                    <div class="label">Completed</div>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="enrollments-section">
                <h2 style="margin: 0 0 20px 0;">All Enrollments</h2>
                
                <div class="search-filter">
                    <input type="text" id="searchInput" placeholder="🔍 Search..." onkeyup="filterTable()">
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div style="overflow-x: auto;">
                    <table class="enrollment-table" id="enrollmentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_enrollments as $enrollment): ?>
                            <tr data-status="<?php echo $enrollment['status']; ?>">
                                <td><?php echo $enrollment['enrollment_id']; ?></td>
                                <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['student_email']); ?></td>
                                <td><strong><?php echo htmlspecialchars($enrollment['course_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($enrollment['instructor_name'] ?? 'N/A'); ?></td>
                                <td><?php echo $enrollment['progress_percentage']; ?>%</td>
                                <td>
                                    <span class="status-badge status-<?php echo $enrollment['status']; ?>">
                                        <?php echo ucfirst($enrollment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                <td>
                                    <button class="action-btn" onclick="unenroll(<?php echo $enrollment['enrollment_id']; ?>, <?php echo $enrollment['user_id']; ?>)">
                                        🗑️ Unenroll
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
            const searchInput = document.getElementById('searchInput').value.toUpperCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('enrollmentsTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const row = tr[i];
                const status = row.getAttribute('data-status');
                const text = row.textContent || row.innerText;

                let showRow = true;

                if (searchInput && text.toUpperCase().indexOf(searchInput) === -1) {
                    showRow = false;
                }

                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            }
        }

        function unenroll(enrollmentId, userId) {
            if (confirm('Are you sure you want to unenroll this student?')) {
                window.location.href = `unenroll-user.php?enrollment_id=${enrollmentId}&user_id=${userId}`;
            }
        }
    </script>
</body>
</html>
