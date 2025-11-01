<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$admin_name = $_SESSION['first_name'];

try {
    $pdo = getDBConnection();
    
    // Get all users with their statistics
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COALESCE(up.total_points, 0) as points,
               COALESCE(up.level, 1) as level,
               (SELECT COUNT(*) FROM course_enrollments WHERE user_id = u.user_id) as enrolled_courses,
               (SELECT COUNT(*) FROM user_certificates WHERE user_id = u.user_id) as certificates
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $all_users = $stmt->fetchAll();
    
    // Get statistics
    $total_users = count($all_users);
    $active_users = count(array_filter($all_users, fn($u) => $u['status'] === 'active'));
    $inactive_users = $total_users - $active_users;
    
} catch (PDOException $e) {
    error_log("Users page error: " . $e->getMessage());
    $all_users = [];
    $total_users = $active_users = $inactive_users = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
        
        .stat-card .label {
            color: #6b7280;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .users-section {
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
            font-size: 14px;
        }
        
        .search-filter input {
            flex: 1;
            min-width: 250px;
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
        
        .role-admin { background: #fee2e2; color: #dc2626; }
        .role-trainer { background: #dbeafe; color: #2563eb; }
        .role-trainee { background: #d1fae5; color: #059669; }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-active { background: #d1fae5; color: #059669; }
        .status-inactive { background: #fee2e2; color: #dc2626; }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
        }
        
        .btn-edit { background: #3b82f6; color: white; }
        .btn-delete { background: #dc2626; color: white; }
        .btn-toggle { background: #6b7280; color: white; }
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
            <a href="../../dashboard/admin/users.php" class="nav-item active">
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
                <h2>👥 Manage Users</h2>
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
                    <div class="number"><?php echo $total_users; ?></div>
                    <div class="label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $active_users; ?></div>
                    <div class="label">Active Users</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $inactive_users; ?></div>
                    <div class="label">Inactive Users</div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="users-section">
                <h2 style="margin: 0 0 20px 0;">All Users</h2>
                
                <div class="search-filter">
                    <input type="text" id="searchInput" placeholder="🔍 Search by name, email..." onkeyup="filterTable()">
                    <select id="roleFilter" onchange="filterTable()">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="trainer">Trainer</option>
                        <option value="trainee">Trainee</option>
                    </select>
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div style="overflow-x: auto;">
                    <table class="user-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Points</th>
                                <th>Level</th>
                                <th>Courses</th>
                                <th>Certificates</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $user): ?>
                            <tr data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
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
                                <td><?php echo $user['certificates']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <button class="action-btn btn-toggle" onclick="toggleStatus(<?php echo $user['user_id']; ?>, '<?php echo $user['status']; ?>')">
                                            <?php echo $user['status'] === 'active' ? '🔒' : '🔓'; ?>
                                        </button>
                                    <?php endif; ?>
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
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('usersTable');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                const row = tr[i];
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                const text = row.textContent || row.innerText;

                let showRow = true;

                if (searchInput && text.toUpperCase().indexOf(searchInput) === -1) {
                    showRow = false;
                }

                if (roleFilter && role !== roleFilter) {
                    showRow = false;
                }

                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            }
        }

        function toggleStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                window.location.href = `toggle-user-status.php?user_id=${userId}&status=${newStatus}`;
            }
        }
    </script>
</body>
</html>
