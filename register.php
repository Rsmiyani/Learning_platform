<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TrainAI</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        /* Additional styles for register page */
        .auth-form-container {
            padding-top: 20px;
            padding-bottom: 60px;
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
            font-size: 14px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            font-size: 14px;
        }
        
        /* Custom scrollbar */
        .auth-right::-webkit-scrollbar {
            width: 8px;
        }
        
        .auth-right::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .auth-right::-webkit-scrollbar-thumb {
            background: #6B5B95;
            border-radius: 4px;
        }
        
        .auth-right::-webkit-scrollbar-thumb:hover {
            background: #5a4a7f;
        }
        
        .interest-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .interest-checkbox:hover {
            border-color: #6B5B95;
            background: #f9fafb;
        }
        
        .interest-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .interest-checkbox input[type="checkbox"]:checked + span {
            font-weight: 600;
            color: #6B5B95;
        }
        
        .interest-checkbox input[type="checkbox"]:checked {
            accent-color: #6B5B95;
        }
        
        .interest-checkbox span {
            font-size: 14px;
            color: #374151;
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Image with Text Overlay -->
        <div class="auth-left">
            <div class="auth-image-container">
                <img src="assets/images/photo.png" alt="Professional Learning Environment" class="auth-image">
                <div class="auth-overlay">
                    <div class="auth-branding">
                        <div class="brand-logo">🎓</div>
                        <h1>TrainAI</h1>
                        <p class="tagline">by DebugThugs</p>
                        
                        <p class="description">
                            Join thousands of learners transforming their training experience 
                            with AI-powered learning management.
                        </p>

                        <div class="auth-stats">
                            <div class="stat-small">
                                <h3>65%</h3>
                                <p>Organizations use us</p>
                            </div>
                            <div class="stat-small">
                                <h3>100%</h3>
                                <p>Success Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Register Form (Keep Same) -->
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Start your learning journey today</p>
                </div>

                <?php
                if (isset($_SESSION['error'])) {
                    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                ?>

                <form action="process_register.php" method="POST" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                placeholder="First name"
                                required
                            >
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                placeholder="Last name"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email"
                            required
                        >
                    </div>

                    <input type="hidden" name="role" value="trainee">

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Create a password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm your password"
                            required
                        >
                    </div>

                    <div class="form-group" id="interests-section" style="display: none;">
                        <label>Select Your Interests (Choose at least 3)</label>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px;">
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Web Development">
                                <span>💻 Web Development</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Data Structures & Algorithms">
                                <span>🧮 DSA</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="C++ Programming">
                                <span>⚙️ C++</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Python Programming">
                                <span>🐍 Python</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Java Programming">
                                <span>☕ Java</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Machine Learning">
                                <span>🤖 Machine Learning</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Data Science">
                                <span>📊 Data Science</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Mobile Development">
                                <span>📱 Mobile Dev</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Cloud Computing">
                                <span>☁️ Cloud Computing</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Cybersecurity">
                                <span>🔒 Cybersecurity</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="DevOps">
                                <span>🔧 DevOps</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="UI/UX Design">
                                <span>🎨 UI/UX Design</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Database Management">
                                <span>🗄️ Databases</span>
                            </label>
                            <label class="interest-checkbox">
                                <input type="checkbox" name="interests[]" value="Artificial Intelligence">
                                <span>🧠 AI</span>
                            </label>
                        </div>
                        <p style="margin-top: 10px; font-size: 12px; color: #6b7280;">Select at least 3 interests to get personalized course recommendations</p>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="terms.php" class="link-inline">Terms & Conditions</a></span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">Create Account</button>

                    <div class="form-divider">
                        <span>or sign up with</span>
                    </div>

                    <div class="social-login">
                        <button type="button" class="social-btn google-btn">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M19.9 10.2c0-.7-.1-1.4-.2-2H10v3.8h5.5c-.2 1.2-1 2.2-2.1 2.9v2.5h3.4c2-1.8 3.1-4.5 3.1-7.2z" fill="#4285F4"/>
                                <path d="M10 20c2.8 0 5.1-.9 6.8-2.5l-3.4-2.5c-.9.6-2.1 1-3.4 1-2.6 0-4.8-1.7-5.6-4.1H.9v2.6C2.6 17.9 6.1 20 10 20z" fill="#34A853"/>
                                <path d="M4.4 11.9c-.2-.6-.3-1.2-.3-1.9s.1-1.3.3-1.9V5.5H.9C.3 6.7 0 8.3 0 10s.3 3.3.9 4.5l3.5-2.6z" fill="#FBBC05"/>
                                <path d="M10 4c1.5 0 2.8.5 3.9 1.5l2.9-2.9C15.1.9 12.8 0 10 0 6.1 0 2.6 2.1.9 5.5l3.5 2.6C5.2 5.7 7.4 4 10 4z" fill="#EA4335"/>
                            </svg>
                            Google
                        </button>
                        <button type="button" class="social-btn github-btn">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 0C4.477 0 0 4.477 0 10c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.35-1.086.636-1.336-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0110 4.836c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C17.137 18.163 20 14.418 20 10c0-5.523-4.477-10-10-10z"/>
                            </svg>
                            GitHub
                        </button>
                    </div>

                    <p class="form-footer">
                        Already have an account? <a href="login.php" class="link-primary">Login</a>
                    </p>
                </form>

                <div class="back-home">
                    <a href="index.html">← Back to Home</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Show interests section for all users (all are trainees)
        document.addEventListener('DOMContentLoaded', function() {
            const interestsSection = document.getElementById('interests-section');
            interestsSection.style.display = 'block';
        });
        
        // Validate at least 3 interests selected
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const checkedInterests = document.querySelectorAll('input[name="interests[]"]:checked');
            if (checkedInterests.length < 3) {
                e.preventDefault();
                alert('Please select at least 3 interests to continue.');
                return false;
            }
        });
    </script>
</body>
</html>
