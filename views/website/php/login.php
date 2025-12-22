<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Candy Crunch</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Modak&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/login.css">
    
</head>
<body>

    <header class="header-section">
        <div class="logo-container">
            <img src="../img/logo.svg" alt="Candy Crunch Logo" class="logo-img">
        </div>
    </header>

    <section class="login-section">
        
        <div class="login-card">
            <button class="close-btn" onclick="window.location.href='landing.php'">&times;</button>

            <div class="login-header">
                <h1 class="login-title">Login</h1>
                <p class="login-subtitle">Welcome back! Nice to meet you again!</p>
                <p class="login-desc">Don't have an account? <a href="sign_up.php" class="signup-link">Sign up</a></p>
            </div>

            <form id="loginForm" class="login-form" autocomplete="on">
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" id="login_input" placeholder="Enter your email" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="login_password" placeholder="Enter password" required autocomplete="current-password">
                        <span class="toggle-password" id="togglePassword">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="button-wrapper">
                    <button type="submit" class="cta-btn full-width" id="btnLogin">
                        Login
                    </button>
                </div>

                <div class="login-footer-links">
                    <a href="forgot_password.php" class="forgot-link">Forgot your password?</a>
                </div>
            </form>
        </div>
    </section>

    <!-- Load JavaScript file -->
    <script src="../js/login.js"></script>
   
</body>
</html>