<?php
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (is_logged_in()) {
    redirect(is_admin() ? '../admin/dashboard.php' : '../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $user = get_user_by_email($email);
        
        if ($user && verify_password($password, $user['password'])) {
            if ($user['is_active']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                update_last_login($user['user_id']);
                
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../index.php');
                }
            } else {
                $error = 'Your account is inactive. Please contact administrator.';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Kafe Latte</title>
    <meta name="description" content="Sign in to your Kafe Latte account for a better shopping experience">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <!-- Form Side -->
        <div class="auth-box">
            <div class="auth-header">
                <div class="logo-auth">
                    <i class="fas fa-coffee"></i>
                    <h1>KAFE LATTE</h1>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to continue your coffee journey</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form" autocomplete="on" id="loginForm">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autocomplete="email"
                        placeholder="your@email.com" 
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to home</a></p>
            </div>
        </div>

        <!-- Image Side -->
        <div class="auth-image">
            <div class="auth-image-overlay">
                <h2>Experience the Best Coffee</h2>
                <p>Join thousands of coffee lovers and discover an unforgettable experience with every cup</p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    // Toggle Password Visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.classList.remove('fa-eye');
            toggleBtn.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleBtn.classList.remove('fa-eye-slash');
            toggleBtn.classList.add('fa-eye');
        }
    }
    
    // Form Submission Handler
    let isSubmitting = false;
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    return false;
                }
                
                isSubmitting = true;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-spinner"></span> <span>Signing in...</span>';
            });
        }
        
        // Auto-focus on email field
        document.getElementById('email').focus();
    });
    
    // Enter key handler
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !isSubmitting) {
            document.getElementById('loginForm').dispatchEvent(new Event('submit'));
        }
    });
    </script>
</body>
</html>