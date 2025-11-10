<?php
require_once '../config/database.php';
initSession();

$cert_id = $_GET['cert_id'] ?? null;

if (!$cert_id) {
    die('Invalid certificate ID');
}

try {
    $pdo = getDBConnection();
    
    // Check if user is admin - admins can view any certificate
    $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    
    if ($is_admin) {
        // Admin can view any certificate
        $stmt = $pdo->prepare("
            SELECT uc.*, u.first_name, u.last_name, c.course_name
            FROM user_certificates uc
            LEFT JOIN users u ON uc.user_id = u.user_id
            LEFT JOIN courses c ON uc.course_id = c.course_id
            WHERE uc.cert_id = ?
        ");
        $stmt->execute([$cert_id]);
    } else {
        // Regular users can only view their own certificates
        $stmt = $pdo->prepare("
            SELECT uc.*, u.first_name, u.last_name, c.course_name
            FROM user_certificates uc
            JOIN users u ON uc.user_id = u.user_id
            JOIN courses c ON uc.course_id = c.course_id
            WHERE uc.cert_id = ? AND uc.user_id = ?
        ");
        $stmt->execute([$cert_id, $_SESSION['user_id']]);
    }
    
    $cert = $stmt->fetch();
    
    if (!$cert) {
        die('Certificate not found or you do not have permission to view it');
    }
    
    // Handle missing data with fallbacks
    $student_name = ($cert['first_name'] ?? 'Unknown') . ' ' . ($cert['last_name'] ?? 'Student');
    $course_name = $cert['course_name'] ?? 'Unknown Course';
    $certificate_code = $cert['certificate_code'] ?? 'N/A';
    $certificate_number = $cert['certificate_number'] ?? 'N/A';
    $issued_date = isset($cert['issued_date']) ? date('F d, Y', strtotime($cert['issued_date'])) : date('F d, Y');

    // Generate HTML Certificate
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Certificate - ' . htmlspecialchars($course_name) . '</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                margin: 0;
                padding: 20px;
                font-family: "Georgia", serif;
                background: #f5f1e8;
            }
            .certificate {
                width: 900px;
                height: 600px;
                background: linear-gradient(135deg, #f5f1e8 0%, #fffaf5 100%);
                border: 3px solid #6B5B95;
                border-radius: 15px;
                padding: 40px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                margin: 20px auto;
                position: relative;
                overflow: hidden;
            }
            @media print {
                body {
                    background: white;
                    padding: 0;
                }
                .certificate {
                    width: 100%;
                    height: 100vh;
                    margin: 0;
                    box-shadow: none;
                    page-break-after: avoid;
                }
                .no-print {
                    display: none !important;
                }
            }
            .certificate::before {
                content: "";
                position: absolute;
                top: -50%;
                right: -50%;
                width: 200%;
                height: 200%;
                background: repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(107, 91, 149, 0.03) 10px,
                    rgba(107, 91, 149, 0.03) 20px
                );
            }
            .content {
                position: relative;
                z-index: 1;
                text-align: center;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .header {
                margin-bottom: 30px;
            }
            .logo {
                font-size: 48px;
                margin-bottom: 10px;
            }
            .title {
                font-size: 48px;
                color: #6B5B95;
                margin: 20px 0;
                letter-spacing: 2px;
                font-weight: bold;
            }
            .subtitle {
                font-size: 18px;
                color: #009B95;
                margin: 10px 0;
            }
            .body {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .recipient {
                font-size: 28px;
                color: #1A1A1A;
                margin: 20px 0;
                font-weight: bold;
            }
            .course-name {
                font-size: 22px;
                color: #6B5B95;
                margin: 20px 0;
                font-style: italic;
            }
            .footer {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                margin-top: 30px;
            }
            .signature {
                text-align: center;
            }
            .date {
                font-size: 14px;
                color: #4A4A4A;
            }
            .cert-id {
                font-size: 12px;
                color: #6B6B6B;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <!-- Action Buttons (hidden when printing) -->
        <div class="no-print" style="text-align: center; margin: 20px 0;">
            <button onclick="window.print()" style="padding: 12px 30px; background: #6B5B95; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin: 0 10px; font-size: 16px;">
                📥 Download as PDF
            </button>
            <button onclick="closeWindow()" style="padding: 12px 30px; background: #e8e4d9; color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin: 0 10px; font-size: 16px;">
                ✕ Close
            </button>
        </div>
        
        <script>
            function closeWindow() {
                // Try to close the window (works if opened by JavaScript)
                window.close();
                
                // If window.close() doesn\'t work, redirect back to certificates page
                setTimeout(function() {
                    window.location.href = "../pages/trainee/certificates.php";
                }, 100);
            }
        </script>
        
        <div class="certificate">
            <div class="content">
                <div class="header">
                    <div class="logo">🎓</div>
                    <div class="title">Certificate of Achievement</div>
                    <div class="subtitle">TrainAI Learning Platform</div>
                </div>
                <div class="body">
                    <p style="font-size: 16px; color: #4A4A4A;">This certifies that</p>
                    <div class="recipient">' . htmlspecialchars($student_name) . '</div>
                    <p style="font-size: 16px; color: #4A4A4A;">has successfully completed the course</p>
                    <div class="course-name">' . htmlspecialchars($course_name) . '</div>
                </div>
                <div class="footer">
                    <div class="date">Date: ' . $issued_date . '</div>
                    <div class="signature">
                        TrainAI<br/>
                        <span style="font-size: 12px;">Learning Excellence</span>
                    </div>
                </div>
                <div class="cert-id">Certificate ID: ' . htmlspecialchars($certificate_number) . '</div>
            </div>
        </div>
    </body>
    </html>
    ';

    // Output as HTML to print
    header('Content-Type: text/html; charset=utf-8');
    echo $html;

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
