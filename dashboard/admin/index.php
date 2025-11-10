<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['first_name'];

try {
    $pdo = getDBConnection();
    
    // Get platform statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'trainee'");
    $stmt->execute();
    $total_students = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'trainer'");
    $stmt->execute();
    $total_trainers = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM courses");
    $stmt->execute();
    $total_courses = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM course_enrollments");
    $stmt->execute();
    $total_enrollments = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user_certificates");
    $stmt->execute();
    $total_certificates = $stmt->fetch()['total'];
    
    // Get recent activities
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, c.course_name, ce.enrolled_at
        FROM course_enrollments ce
        JOIN users u ON ce.user_id = u.user_id
        JOIN courses c ON ce.course_id = c.course_id
        ORDER BY ce.enrolled_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_enrollments = $stmt->fetchAll();
    
    // Get all users for management
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COALESCE(up.total_points, 0) as points,
               COALESCE(up.level, 1) as level,
               (SELECT COUNT(*) FROM course_enrollments WHERE user_id = u.user_id) as enrolled_courses
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $all_users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $total_students = $total_trainers = $total_courses = $total_enrollments = $total_certificates = 0;
    $recent_enrollments = $all_users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .admin-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .admin-stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .admin-stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .admin-stat-box .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .admin-stat-box .number {
            font-size: 36px;
            font-weight: bold;
            color: #1f2937;
            margin: 10px 0;
        }
        
        .admin-stat-box .label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .admin-section h2 {
            margin: 0 0 20px 0;
            color: #1f2937;
            font-size: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .user-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .user-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .user-table tr:hover {
            background: #f9fafb;
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .role-admin {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .role-trainer {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .role-trainee {
            background: #d1fae5;
            color: #059669;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d1fae5;
            color: #059669;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            transition: all 0.3s;
        }
        
        .btn-points {
            background: #fbbf24;
            color: #78350f;
        }
        
        .btn-points:hover {
            background: #f59e0b;
        }
        
        .btn-unenroll {
            background: #dc2626;
            color: white;
        }
        
        .btn-unenroll:hover {
            background: #991b1b;
        }
        
        .btn-certificates {
            background: #8b5cf6;
            color: white;
        }
        
        .btn-certificates:hover {
            background: #7c3aed;
        }
        
        .btn-toggle {
            background: #6b7280;
            color: white;
        }
        
        .btn-toggle:hover {
            background: #4b5563;
        }
        
        .activity-item {
            padding: 12px;
            border-left: 3px solid #3b82f6;
            background: #f0f9ff;
            margin-bottom: 10px;
            border-radius: 6px;
        }
        
        .activity-item .time {
            font-size: 12px;
            color: #6b7280;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .quick-action-btn {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.3s;
            text-align: center;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/admin/" class="sidebar-logo">
                <span class="logo-icon">👑</span>
                <span class="logo-text">TrainAI Admin</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/admin/" class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../dashboard/admin/users.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Manage Users</span>
            </a>
            <a href="../../dashboard/admin/trainer-requests.php" class="nav-item">
                <span class="nav-icon">👨‍🏫</span>
                <span class="nav-text">Trainer Requests</span>
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>👑 Admin Dashboard</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #d1fae5; color: #059669; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #059669;">
                    ✅ <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc2626;">
                    ❌ <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Admin Header -->
            <div class="admin-header">
                <h1>Welcome, <?php echo htmlspecialchars($admin_name); ?>! 👑</h1>
                <p>You have full control over the TrainAI platform</p>
            </div>

            <!-- Statistics -->
            <div class="admin-stats">
                <div class="admin-stat-box">
                    <div class="icon">👨‍🎓</div>
                    <div class="number"><?php echo $total_students; ?></div>
                    <div class="label">Total Students</div>
                </div>
                <div class="admin-stat-box">
                    <div class="icon">👨‍🏫</div>
                    <div class="number"><?php echo $total_trainers; ?></div>
                    <div class="label">Total Trainers</div>
                </div>
                <div class="admin-stat-box">
                    <div class="icon">📚</div>
                    <div class="number"><?php echo $total_courses; ?></div>
                    <div class="label">Total Courses</div>
                </div>
                <div class="admin-stat-box">
                    <div class="icon">📝</div>
                    <div class="number"><?php echo $total_enrollments; ?></div>
                    <div class="label">Total Enrollments</div>
                </div>
                <div class="admin-stat-box">
                    <div class="icon">🎓</div>
                    <div class="number"><?php echo $total_certificates; ?></div>
                    <div class="label">Certificates Issued</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="quick-action-btn" onclick="window.location.href='users.php'">
                    👥 Manage Users
                </button>
                <button class="quick-action-btn" onclick="window.location.href='courses.php'">
                    📚 Manage Courses
                </button>
                <button class="quick-action-btn" onclick="window.location.href='enrollments.php'">
                    📝 View Enrollments
                </button>
                <button class="quick-action-btn" onclick="window.location.href='certificates.php'">
                    🎓 Manage Certificates
                </button>
            </div>

            <!-- User Management Section -->
            <div class="admin-section">
                <h2>👥 User Management</h2>
                
                <div class="search-box">
                    <input type="text" id="userSearch" placeholder="🔍 Search users by name, email, or role..." onkeyup="filterUsers()">
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="user-table" id="userTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Points</th>
                                <th>Level</th>
                                <th>Courses</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['points']; ?></td>
                                <td>Level <?php echo $user['level']; ?></td>
                                <td><?php echo $user['enrolled_courses']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <button class="action-btn btn-points" onclick="managePoints(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name']); ?>')">
                                            ⭐ Points
                                        </button>
                                        <?php if ($user['role'] === 'trainee'): ?>
                                            <button class="action-btn btn-unenroll" onclick="viewEnrollments(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name']); ?>')">
                                                📝 Enrollments
                                            </button>
                                            <button class="action-btn btn-certificates" onclick="viewCertificates(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name']); ?>')">
                                                🎓 Certificates
                                            </button>
                                        <?php endif; ?>
                                        <button class="action-btn btn-toggle" onclick="toggleUserStatus(<?php echo $user['user_id']; ?>, '<?php echo $user['status']; ?>')">
                                            <?php echo $user['status'] === 'active' ? '🔒 Deactivate' : '🔓 Activate'; ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="admin-section">
                <h2>📊 Recent Enrollments</h2>
                <?php if (count($recent_enrollments) > 0): ?>
                    <?php foreach ($recent_enrollments as $enrollment): ?>
                        <div class="activity-item">
                            <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                            enrolled in
                            <strong><?php echo htmlspecialchars($enrollment['course_name']); ?></strong>
                            <div class="time"><?php echo date('M d, Y H:i', strtotime($enrollment['enrolled_at'])); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #6b7280; text-align: center; padding: 20px;">No recent enrollments</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
    <script>
        // Filter users
        function filterUsers() {
            const input = document.getElementById('userSearch');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('userTable');
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

        // Manage Points
        function managePoints(userId, userName) {
            const points = prompt(`Enter points to add/remove for ${userName}:\n(Use negative number to remove points)`);
            if (points !== null && points !== '') {
                window.location.href = `manage-points.php?user_id=${userId}&points=${points}`;
            }
        }

        // View Enrollments
        function viewEnrollments(userId, userName) {
            window.location.href = `user-enrollments.php?user_id=${userId}`;
        }

        // View Certificates
        function viewCertificates(userId, userName) {
            window.location.href = `user-certificates.php?user_id=${userId}`;
        }

        // Toggle User Status
        function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                window.location.href = `toggle-user-status.php?user_id=${userId}&status=${newStatus}`;
            }
        }
    </script>
</body>
</html>
