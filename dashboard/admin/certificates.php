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
    
    // Get all certificates with explicit column names (using LEFT JOIN to show all certificates)
    // Note: Using cert_id as the primary key column name based on database structure
    $stmt = $pdo->prepare("
        SELECT uc.cert_id as certificate_id, uc.user_id, uc.course_id, 
               uc.certificate_code, uc.certificate_number, uc.issued_date,
               COALESCE(c.course_name, 'Unknown Course') as course_name, 
               COALESCE(c.course_code, 'N/A') as course_code,
               COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Unknown User') as student_name,
               COALESCE(u.email, 'N/A') as student_email
        FROM user_certificates uc
        LEFT JOIN courses c ON uc.course_id = c.course_id
        LEFT JOIN users u ON uc.user_id = u.user_id
        ORDER BY uc.issued_date DESC
    ");
    $stmt->execute();
    $all_certificates = $stmt->fetchAll();
    
    // Get statistics
    $total_certificates = count($all_certificates);
    $this_month = count(array_filter($all_certificates, fn($c) => date('Y-m', strtotime($c['issued_date'])) === date('Y-m')));
    $this_year = count(array_filter($all_certificates, fn($c) => date('Y', strtotime($c['issued_date'])) === date('Y')));
    
} catch (PDOException $e) {
    error_log("Certificates page error: " . $e->getMessage());
    $all_certificates = [];
    $total_certificates = $this_month = $this_year = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - Admin</title>
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
        
        .certificates-section {
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
        
        .certificate-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .certificate-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .certificate-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .certificate-table tr:hover {
            background: #f9fafb;
        }
        
        .cert-code {
            font-family: monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
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
            <a href="../../dashboard/admin/courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                <span class="nav-text">Manage Courses</span>
            </a>
            <a href="../../dashboard/admin/enrollments.php" class="nav-item">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Enrollments</span>
            </a>
            <a href="../../dashboard/admin/certificates.php" class="nav-item active">
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
                <h2>🎓 Certificates</h2>
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
                    <div class="number"><?php echo $total_certificates; ?></div>
                    <div class="label">Total Certificates</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $this_month; ?></div>
                    <div class="label">This Month</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $this_year; ?></div>
                    <div class="label">This Year</div>
                </div>
            </div>

            <!-- Certificates Table -->
            <div class="certificates-section">
                <h2 style="margin: 0 0 20px 0;">All Certificates</h2>
                
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="🔍 Search certificates..." onkeyup="filterTable()">
                </div>

                <div style="overflow-x: auto;">
                    <table class="certificate-table" id="certificatesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Course</th>
                                <th>Certificate Code</th>
                                <th>Certificate Number</th>
                                <th>Issued Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_certificates as $cert): ?>
                            <tr>
                                <td><?php echo isset($cert['certificate_id']) ? $cert['certificate_id'] : (isset($cert['cert_id']) ? $cert['cert_id'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cert['student_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($cert['student_email'] ?? 'N/A'); ?></td>
                                <td><strong><?php echo htmlspecialchars($cert['course_name'] ?? 'N/A'); ?></strong></td>
                                <td><span class="cert-code"><?php echo htmlspecialchars($cert['certificate_code'] ?? 'N/A'); ?></span></td>
                                <td><span class="cert-code"><?php echo htmlspecialchars($cert['certificate_number'] ?? 'N/A'); ?></span></td>
                                <td><?php echo isset($cert['issued_date']) ? date('M d, Y', strtotime($cert['issued_date'])) : 'N/A'; ?></td>
                                <td>
                                    <?php 
                                    $cert_id = isset($cert['certificate_id']) ? $cert['certificate_id'] : (isset($cert['cert_id']) ? $cert['cert_id'] : null);
                                    $user_id = $cert['user_id'] ?? null;
                                    if ($cert_id && $user_id): 
                                    ?>
                                        <a href="../../handlers/generate-certificate.php?cert_id=<?php echo $cert_id; ?>" 
                                           class="action-btn btn-view" target="_blank">
                                            📄 View
                                        </a>
                                        <button class="action-btn btn-delete" 
                                                onclick="deleteCert(<?php echo $cert_id; ?>, <?php echo $user_id; ?>)">
                                            🗑️ Delete
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
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('certificatesTable');
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

        function deleteCert(certId, userId) {
            if (confirm('Are you sure you want to delete this certificate? This action cannot be undone.')) {
                window.location.href = `delete-certificate.php?cert_id=${certId}&user_id=${userId}`;
            }
        }
    </script>
</body>
</html>
