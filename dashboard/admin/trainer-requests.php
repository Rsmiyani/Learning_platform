<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['first_name'];

$filter = $_GET['filter'] ?? 'all'; // all, pending, approved, rejected
$requests = [];

try {
    $pdo = getDBConnection();
    
    // Build query based on filter
    $query = "
        SELECT tr.*, 
               u.first_name, 
               u.last_name, 
               u.email,
               u.role as user_role,
               u.created_at as user_created_at,
               admin.first_name as reviewer_first_name,
               admin.last_name as reviewer_last_name
        FROM trainer_requests tr
        INNER JOIN users u ON tr.user_id = u.user_id
        LEFT JOIN users admin ON tr.reviewed_by = admin.user_id
    ";
    
    if ($filter !== 'all') {
        $query .= " WHERE tr.status = ?";
    }
    $query .= " ORDER BY tr.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    if ($filter !== 'all') {
        $stmt->execute([$filter]);
    } else {
        $stmt->execute();
    }
    
    $requests = $stmt->fetchAll();
    
    // Get statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM trainer_requests WHERE status = ?");
    $stats = [
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'total' => 0
    ];
    
    foreach (['pending', 'approved', 'rejected'] as $status) {
        $stmt->execute([$status]);
        $stats[$status] = $stmt->fetch()['count'];
    }
    $stats['total'] = array_sum($stats);
    
} catch (PDOException $e) {
    error_log("Trainer requests error: " . $e->getMessage());
    $requests = [];
    $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0];
    $debug_info = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Requests - TrainAI Admin</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-box .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .stat-box .number {
            font-size: 36px;
            font-weight: bold;
            color: #1f2937;
            margin: 10px 0;
        }
        
        .stat-box .label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 10px 20px;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            border-color: #dc2626;
            color: #dc2626;
        }
        
        .filter-tab.active {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
        }
        
        .requests-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .requests-table th {
            background: #f3f4f6;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .requests-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .requests-table tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-approve {
            background: #10b981;
            color: white;
        }
        
        .btn-approve:hover {
            background: #059669;
        }
        
        .btn-reject {
            background: #ef4444;
            color: white;
        }
        
        .btn-reject:hover {
            background: #dc2626;
        }
        
        .request-message {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .modal-content h3 {
            margin: 0 0 20px 0;
            color: #1f2937;
        }
        
        .modal-content textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 20px;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
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
            <a href="../../dashboard/admin/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../dashboard/admin/users.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Manage Users</span>
            </a>
            <a href="../../dashboard/admin/trainer-requests.php" class="nav-item active">
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
                <h2>👨‍🏫 Trainer Requests</h2>
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
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="icon">📋</div>
                    <div class="number"><?php echo $stats['total']; ?></div>
                    <div class="label">Total Requests</div>
                </div>
                <div class="stat-box">
                    <div class="icon">⏳</div>
                    <div class="number"><?php echo $stats['pending']; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-box">
                    <div class="icon">✅</div>
                    <div class="number"><?php echo $stats['approved']; ?></div>
                    <div class="label">Approved</div>
                </div>
                <div class="stat-box">
                    <div class="icon">❌</div>
                    <div class="number"><?php echo $stats['rejected']; ?></div>
                    <div class="label">Rejected</div>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    All Requests
                </a>
                <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                    Pending (<?php echo $stats['pending']; ?>)
                </a>
                <a href="?filter=approved" class="filter-tab <?php echo $filter === 'approved' ? 'active' : ''; ?>">
                    Approved (<?php echo $stats['approved']; ?>)
                </a>
                <a href="?filter=rejected" class="filter-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">
                    Rejected (<?php echo $stats['rejected']; ?>)
                </a>
            </div>
            
            <!-- Requests Table -->
            <?php if (count($requests) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Current Role</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Reviewed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo $request['request_id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['user_role']; ?>">
                                            <?php echo ucfirst($request['user_role']); ?>
                                        </span>
                                    </td>
                                    <td class="request-message" title="<?php echo htmlspecialchars($request['request_message'] ?? 'No message'); ?>">
                                        <?php echo htmlspecialchars($request['request_message'] ?? 'No message'); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $request['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => '⏳ Pending',
                                                'approved' => '✅ Approved',
                                                'rejected' => '❌ Rejected'
                                            ];
                                            echo $status_labels[$request['status']];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <?php if ($request['reviewer_first_name']): ?>
                                            <?php echo htmlspecialchars($request['reviewer_first_name'] . ' ' . $request['reviewer_last_name']); ?>
                                            <br>
                                            <small style="color: #6b7280;">
                                                <?php echo date('M d, Y', strtotime($request['reviewed_at'])); ?>
                                            </small>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <div class="action-buttons">
                                                <a href="#" 
                                                   class="btn-action btn-approve" 
                                                   onclick="approveRequest(<?php echo $request['request_id']; ?>); return false;">
                                                    ✅ Approve
                                                </a>
                                                <a href="#" 
                                                   class="btn-action btn-reject" 
                                                   onclick="rejectRequest(<?php echo $request['request_id']; ?>); return false;">
                                                    ❌ Reject
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <?php if ($request['admin_note']): ?>
                                                <small style="color: #6b7280;" title="<?php echo htmlspecialchars($request['admin_note']); ?>">
                                                    📝 Note added
                                                </small>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">-</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>No trainer requests found</h3>
                    <p>There are no trainer requests matching your current filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal for Admin Note -->
    <div id="actionModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">Action</h3>
            <form id="actionForm" method="POST" action="">
                <textarea 
                    id="adminNote" 
                    name="admin_note" 
                    placeholder="Add a note (optional)..."
                ></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn-action btn-reject" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-action btn-approve" id="submitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../../assets/js/dashboard.js"></script>
    <script>
        let currentRequestId = null;
        let currentAction = '';
        
        function approveRequest(requestId) {
            currentRequestId = requestId;
            currentAction = 'approve';
            document.getElementById('modalTitle').textContent = 'Approve Trainer Request';
            document.getElementById('submitBtn').textContent = 'Approve';
            document.getElementById('submitBtn').className = 'btn-action btn-approve';
            document.getElementById('actionForm').action = `../../handlers/process-trainer-request.php?request_id=${requestId}&action=approve`;
            document.getElementById('actionModal').style.display = 'block';
        }
        
        function rejectRequest(requestId) {
            currentRequestId = requestId;
            currentAction = 'reject';
            document.getElementById('modalTitle').textContent = 'Reject Trainer Request';
            document.getElementById('submitBtn').textContent = 'Reject';
            document.getElementById('submitBtn').className = 'btn-action btn-reject';
            document.getElementById('actionForm').action = `../../handlers/process-trainer-request.php?request_id=${requestId}&action=reject`;
            document.getElementById('actionModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('actionModal').style.display = 'none';
            document.getElementById('adminNote').value = '';
            currentRequestId = null;
            currentAction = '';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('actionModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

