<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'User';
$success_message = '';
$error_message = '';

try {
    $pdo = getDBConnection();
    
    // Check if user already has a pending or approved request
    $stmt = $pdo->prepare("
        SELECT * FROM trainer_requests 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $existing_request = $stmt->fetch();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
        // Check if there's already a pending request
        if ($existing_request && $existing_request['status'] === 'pending') {
            $error_message = "You already have a pending trainer request. Please wait for admin approval.";
        } else {
            $request_message = trim($_POST['request_message'] ?? '');
            
            // Insert new request
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO trainer_requests (user_id, request_message, status, created_at)
                    VALUES (?, ?, 'pending', NOW())
                ");
                
                if ($stmt->execute([$user_id, $request_message ?: null])) {
                    $success_message = "Your trainer request has been submitted successfully! Admin will review it soon.";
                    
                    // Re-fetch the newly created request to display it
                    $stmt = $pdo->prepare("
                        SELECT * FROM trainer_requests 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ");
                    $stmt->execute([$user_id]);
                    $existing_request = $stmt->fetch();
                } else {
                    $error_message = "Failed to submit request. Please try again.";
                }
            } catch (PDOException $e) {
                error_log("Insert trainer request error: " . $e->getMessage());
                $error_message = "An error occurred while submitting your request: " . $e->getMessage();
            }
        }
    }
    
} catch (PDOException $e) {
    error_log("Become trainer error: " . $e->getMessage());
    $error_message = "An error occurred. Please try again.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Trainer - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .become-trainer-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .request-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .request-card h2 {
            margin: 0 0 10px 0;
            color: #1f2937;
            font-size: 24px;
        }
        
        .request-card p {
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #6B5B95;
            box-shadow: 0 0 0 3px rgba(107, 91, 149, 0.1);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6B5B95 0%, #4a3d6f 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(107, 91, 149, 0.3);
        }
        
        .btn-submit:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            margin: 0;
            color: #1e40af;
        }
        
        .request-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .request-details h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
        }
        
        .request-details p {
            margin: 8px 0;
            color: #4b5563;
        }
        
        .admin-note {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
        
        .admin-note strong {
            color: #92400e;
        }
    </style>
</head>
<body>
    <?php include '../../includes/trainee-sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>👨‍🏫 Become a Trainer</h2>
            </div>
        </div>
        
        <div class="dashboard-container">
            <div class="become-trainer-container">
                <?php if ($success_message): ?>
                    <div style="background: #d1fae5; color: #059669; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #059669;">
                        ✅ <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc2626;">
                        ❌ <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="request-card">
                    <h2>👨‍🏫 Become a Trainer</h2>
                    <p>
                        Want to share your knowledge and create courses? Submit a request to become a trainer. 
                        Our admin team will review your request and get back to you soon.
                    </p>
                    
                    <?php if ($existing_request): ?>
                        <?php
                        $status = $existing_request['status'];
                        $status_labels = [
                            'pending' => '⏳ Pending Review',
                            'approved' => '✅ Approved',
                            'rejected' => '❌ Rejected'
                        ];
                        ?>
                        <div class="status-badge status-<?php echo $status; ?>">
                            <?php echo $status_labels[$status]; ?>
                        </div>
                        
                        <div class="request-details">
                            <h3>Your Request Details</h3>
                            <p><strong>Submitted:</strong> <?php echo date('F d, Y g:i A', strtotime($existing_request['created_at'])); ?></p>
                            
                            <?php if ($existing_request['request_message']): ?>
                                <p><strong>Your Message:</strong></p>
                                <p style="background: white; padding: 10px; border-radius: 6px; margin-top: 10px;">
                                    <?php echo nl2br(htmlspecialchars($existing_request['request_message'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($existing_request['reviewed_at']): ?>
                                <p><strong>Reviewed:</strong> <?php echo date('F d, Y g:i A', strtotime($existing_request['reviewed_at'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($existing_request['admin_note']): ?>
                                <div class="admin-note">
                                    <strong>Admin Note:</strong>
                                    <p style="margin-top: 8px; color: #78350f;">
                                        <?php echo nl2br(htmlspecialchars($existing_request['admin_note'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($status === 'approved'): ?>
                            <div class="info-box" style="margin-top: 20px;">
                                <p>
                                    🎉 Congratulations! Your request has been approved. You can now access the trainer dashboard 
                                    and start creating courses. Please log out and log back in to see the trainer features.
                                </p>
                            </div>
                        <?php elseif ($status === 'rejected'): ?>
                            <div class="info-box" style="background: #fee2e2; border-color: #dc2626; margin-top: 20px;">
                                <p style="color: #991b1b;">
                                    Your request has been rejected. If you have questions, please contact the admin team.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="info-box" style="margin-top: 20px;">
                                <p>
                                    Your request is currently under review. We'll notify you once a decision has been made.
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="request_message">Why do you want to become a trainer? (Optional)</label>
                                <textarea 
                                    id="request_message" 
                                    name="request_message" 
                                    placeholder="Tell us about your experience, expertise, and why you'd like to become a trainer. This will help us review your request faster."
                                ></textarea>
                            </div>
                            
                            <button type="submit" name="submit_request" class="btn-submit">
                                Submit Request
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <div class="request-card">
                    <h3>What you'll get as a trainer:</h3>
                    <ul style="color: #4b5563; line-height: 2;">
                        <li>✅ Create and manage your own courses</li>
                        <li>✅ Add modules, lessons, and exams</li>
                        <li>✅ Track student progress and enrollments</li>
                        <li>✅ View analytics and performance metrics</li>
                        <li>✅ Manage your students and their progress</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>

