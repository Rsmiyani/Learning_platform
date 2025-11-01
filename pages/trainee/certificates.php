<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'User';

try {
    $pdo = getDBConnection();
    
    // Get user certificates
    $stmt = $pdo->prepare("
        SELECT uc.*, c.course_name, c.course_code
        FROM user_certificates uc
        JOIN courses c ON uc.course_id = c.course_id
        WHERE uc.user_id = ?
        ORDER BY uc.issued_date DESC
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll();
    
} catch (Exception $e) {
    $certificates = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainee/" class="sidebar-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">TrainAI</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/trainee/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../pages/trainee/my-courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                <span class="nav-text">My Courses</span>
            </a>
            <a href="../../pages/trainee/achievements.php" class="nav-item">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Achievements</span>
            </a>
            <a href="../../pages/trainee/certificates.php" class="nav-item active">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Certificates</span>
            </a>
            <a href="../../pages/trainee/analytics.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Analytics</span>
            </a>
            <a href="../../pages/trainee/settings.php" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
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
                <h2 style="margin: 0; color: var(--text-primary);">🎓 Certificates</h2>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($first_name); ?></p>
                        <p class="user-level">Trainee</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="welcome-banner">
                <div class="banner-content">
                    <h1>Your Certificates</h1>
                    <p>Professional credentials for your completed courses</p>
                </div>
            </div>

            <!-- Certificates List -->
            <div style="margin-top: 30px;">
                <h2 style="margin-bottom: 20px; color: var(--text-primary);">Certificates (<?php echo count($certificates); ?>)</h2>
                
                <?php if (count($certificates) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                        <?php foreach ($certificates as $cert): ?>
                        <div class="card premium-card" style="border-top: 3px solid linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));">
                            <div style="padding: 30px; text-align: center; position: relative; background: linear-gradient(135deg, rgba(107, 91, 149, 0.05) 0%, rgba(0, 155, 149, 0.05) 100%); border-radius: 10px;">
                                <!-- Certificate Design -->
                                <div style="font-size: 64px; margin-bottom: 15px;">🎖️</div>
                                
                                <h3 style="margin: 15px 0; color: var(--accent-primary); font-size: 1.5rem;">
                                    <?php echo htmlspecialchars($cert['course_name']); ?>
                                </h3>
                                
                                <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">
                                    Course Code: <?php echo htmlspecialchars($cert['course_code']); ?>
                                </p>
                                
                                <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 8px; border: 2px dashed var(--border-color);">
                                    <p style="margin: 5px 0; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Certificate ID</p>
                                    <p style="margin: 5px 0; color: var(--text-primary); font-weight: 700; font-size: 1.1rem; font-family: monospace;">
                                        <?php echo htmlspecialchars($cert['certificate_number']); ?>
                                    </p>
                                </div>
                                
                                <p style="margin: 15px 0; color: var(--text-muted); font-size: 0.875rem;">
                                    Issued: <?php echo date('F d, Y', strtotime($cert['issued_date'])); ?>
                                </p>
                                
                                <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <button onclick="viewCertificate(<?php echo htmlspecialchars($cert['cert_id']); ?>)" style="padding: 12px; background: var(--border-color); color: var(--text-primary); border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                        👁️ View
                                    </button>
                                    <button onclick="downloadCertificate(<?php echo htmlspecialchars($cert['cert_id']); ?>)" style="padding: 12px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                                        📥 Download
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card premium-card">
                        <div style="padding: 60px 40px; text-align: center;">
                            <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">🎓</div>
                            <h3 style="margin: 20px 0; color: var(--text-primary);">No Certificates Yet</h3>
                            <p style="margin: 10px 0; color: var(--text-muted); font-size: 1rem;">
                                Complete courses to earn certificates and showcase your achievements!
                            </p>
                            <a href="../../dashboard/trainee/" style="margin-top: 20px; display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                Continue Learning →
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // View certificate in new tab
        function viewCertificate(certId) {
            window.open(`../../handlers/generate-certificate.php?cert_id=${certId}`, '_blank');
        }

        // Download certificate as PDF (using print dialog)
        function downloadCertificate(certId) {
            const printWindow = window.open(`../../handlers/generate-certificate.php?cert_id=${certId}&download=1`, '_blank');
            
            // Wait for the window to load, then trigger print
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            };
        }
    </script>
    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>
