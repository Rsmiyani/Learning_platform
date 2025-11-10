<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

try {
    $pdo = getDBConnection();
    
    // Get trainer details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$trainer_id]);
    $trainer = $stmt->fetch();
    
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error_message = "All fields are required!";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
            if ($stmt->execute([$first_name, $last_name, $email, $trainer_id])) {
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $success_message = "Profile updated successfully!";
                $trainer['first_name'] = $first_name;
                $trainer['last_name'] = $last_name;
                $trainer['email'] = $email;
            }
        }
    }
    
    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All password fields are required!";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match!";
        } elseif (strlen($new_password) < 6) {
            $error_message = "Password must be at least 6 characters!";
        } else {
            // Verify current password
            if (password_verify($current_password, $trainer['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($stmt->execute([$hashed_password, $trainer_id])) {
                    $success_message = "Password changed successfully!";
                }
            } else {
                $error_message = "Current password is incorrect!";
            }
        }
    }
    
} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .main-content {
            height: 100vh;
            overflow-y: auto;
        }
        
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            padding-bottom: 60px;
        }
        
        .settings-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .settings-card h2 {
            margin: 0 0 20px 0;
            color: #1f2937;
            font-size: 1.5rem;
            border-bottom: 2px solid #4a9d9a;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #4a9d9a;
            box-shadow: 0 0 0 3px rgba(74, 157, 154, 0.1);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #4a9d9a, #2d7a77);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 157, 154, 0.3);
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            margin: 5px 0;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <?php include '../../includes/trainer-sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h2>⚙️ Settings</h2>
            </div>
        </div>
        
        <div class="settings-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success">✓ <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">✗ <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Profile Settings -->
            <div class="settings-card">
                <h2>👤 Profile Information</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($trainer['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($trainer['last_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($trainer['email']); ?>" required>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-save">💾 Save Changes</button>
                </form>
            </div>
            
            <!-- Password Settings -->
            <div class="settings-card">
                <h2>🔒 Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" minlength="6" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-save">🔑 Change Password</button>
                </form>
            </div>
            
            <!-- Account Info -->
            <div class="settings-card">
                <h2>📋 Account Information</h2>
                <div class="info-box">
                    <p><strong>User ID:</strong> <?php echo $trainer['user_id']; ?></p>
                    <p><strong>Role:</strong> Trainer</p>
                    <p><strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($trainer['created_at'])); ?></p>
                    <p><strong>Last Login:</strong> <?php echo $trainer['last_login'] ? date('F d, Y H:i', strtotime($trainer['last_login'])) : 'N/A'; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
