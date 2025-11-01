<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header('Location: index.php');
        exit;
    }
    
    // Get all certificates
    $stmt = $pdo->prepare("
        SELECT uc.*, c.course_name, c.course_code
        FROM user_certificates uc
        JOIN courses c ON uc.course_id = c.course_id
        WHERE uc.user_id = ?
        ORDER BY uc.issued_date DESC
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Certificate fetch error: " . $e->getMessage());
    $certificates = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Certificates - Admin</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .back-btn {
            background: #6b7280;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .back-btn:hover {
            background: #4b5563;
        }
        
        .user-info {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .user-info h2 {
            margin: 0 0 10px 0;
        }
        
        .certificate-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #8b5cf6;
        }
        
        .certificate-info h3 {
            margin: 0 0 5px 0;
            color: #1f2937;
        }
        
        .certificate-info p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .certificate-actions {
            display: flex;
            gap: 10px;
        }
        
        .view-btn {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }
        
        .view-btn:hover {
            background: #2563eb;
        }
        
        .delete-btn {
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .delete-btn:hover {
            background: #991b1b;
        }
        
        .cert-code {
            font-family: monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
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
            <a href="../../dashboard/admin/certificates.php" class="nav-item active">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Certificates</span>
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
                <h2>🎓 Manage Certificates</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <a href="index.php" class="back-btn">← Back to Dashboard</a>
            
            <div class="user-info">
                <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Total Certificates:</strong> <?php echo count($certificates); ?></p>
            </div>

            <?php if (count($certificates) > 0): ?>
                <?php foreach ($certificates as $cert): ?>
                    <div class="certificate-card">
                        <div class="certificate-info">
                            <h3>🎓 <?php echo htmlspecialchars($cert['course_name']); ?></h3>
                            <p><strong>Course Code:</strong> <?php echo htmlspecialchars($cert['course_code']); ?></p>
                            <p><strong>Certificate Code:</strong> <span class="cert-code"><?php echo htmlspecialchars($cert['certificate_code']); ?></span></p>
                            <p><strong>Certificate Number:</strong> <span class="cert-code"><?php echo htmlspecialchars($cert['certificate_number']); ?></span></p>
                            <p><strong>Issued:</strong> <?php echo date('M d, Y', strtotime($cert['issued_date'])); ?></p>
                        </div>
                        <div class="certificate-actions">
                            <a href="../../handlers/generate-certificate.php?cert_id=<?php echo $cert['certificate_id']; ?>" class="view-btn" target="_blank">
                                📄 View
                            </a>
                            <button class="delete-btn" onclick="deleteCertificate(<?php echo $cert['certificate_id']; ?>, '<?php echo htmlspecialchars($cert['course_name']); ?>')">
                                🗑️ Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
                    <h3 style="color: #6b7280;">No certificates found</h3>
                    <p style="color: #9ca3af;">This user has not earned any certificates yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteCertificate(certId, courseName) {
            if (confirm(`Are you sure you want to delete the certificate for "${courseName}"?\n\nThis action cannot be undone and the user will lose their certificate permanently.`)) {
                window.location.href = `delete-certificate.php?cert_id=${certId}&user_id=<?php echo $user_id; ?>`;
            }
        }
    </script>
</body>
</html>
