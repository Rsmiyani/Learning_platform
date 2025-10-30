<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TrainAI</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Image with Text Overlay (Same as Register) -->
        <div class="auth-left">
            <div class="auth-image-container">
                <img src="assets/images/photo.png" alt="Professional Learning Environment" class="auth-image">
                <div class="auth-overlay">
                    <div class="auth-branding">
                        <div class="brand-logo">🎓</div>
                        <h1>TrainAI</h1>
                        <p class="tagline">by DebugThugs</p>
                        
                        <p class="description">
                            Transform your training with AI-powered learning management. 
                            Track progress, conduct exams, and get personalized recommendations.
                        </p>

                        <div class="auth-stats">
                            <div class="stat-small">
                                <h3>60%</h3>
                                <p>Higher Completion</p>
                            </div>
                            <div class="stat-small">
                                <h3>50%</h3>
                                <p>Faster Reports</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Welcome Back</h2>
                    <p>Login to access your training dashboard</p>
                </div>

                <form action="process_login.php" method="POST" class="auth-form">
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

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-submit">Login</button>

                    <div class="form-divider">
                        <span>or continue with</span>
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
                        Don't have an account? <a href="register.php" class="link-primary">Sign up</a>
                    </p>
                </form>

                <div class="back-home">
                    <a href="index.html">← Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
