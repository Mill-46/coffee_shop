<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (isLoggedIn()) {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if email already exists
        $checkQuery = "SELECT user_id FROM users WHERE email = :email";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $error = 'Email already registered';
        } else {
            try {
                $db->beginTransaction();
                
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userQuery = "INSERT INTO users (email, password, role) VALUES (:email, :password, 'user')";
                $userStmt = $db->prepare($userQuery);
                $userStmt->bindParam(':email', $email);
                $userStmt->bindParam(':password', $hashedPassword);
                $userStmt->execute();
                
                $user_id = $db->lastInsertId();
                
                // Create customer profile
                $customerQuery = "INSERT INTO customers (user_id, full_name, email, phone_number) 
                                 VALUES (:user_id, :full_name, :email, :phone)";
                $customerStmt = $db->prepare($customerQuery);
                $customerStmt->bindParam(':user_id', $user_id);
                $customerStmt->bindParam(':full_name', $full_name);
                $customerStmt->bindParam(':email', $email);
                $customerStmt->bindParam(':phone', $phone);
                $customerStmt->execute();
                
                $customer_id = $db->lastInsertId();
                
                // Update user with customer_id
                $updateQuery = "UPDATE users SET customer_id = :customer_id WHERE user_id = :user_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':customer_id', $customer_id);
                $updateStmt->bindParam(':user_id', $user_id);
                $updateStmt->execute();
                
                $db->commit();
                
                $success = 'Registration successful! You will be automatically logged in...';
                
                // Auto login
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                $_SESSION['user_name'] = $full_name;
                
                header("refresh:2;url=../index.php");
                
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Kafe Latte</title>
    <meta name="description" content="Create your Kafe Latte account for a better shopping experience">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container register-container">
        <!-- Form Side -->
        <div class="auth-box register-box">
            <div class="auth-header">
                <div class="logo-auth">
                    <i class="fas fa-user-plus"></i>
                    <h1>KAFE LATTE</h1>
                </div>
                <h2>Join Us Today</h2>
                <p>Create your account and start your coffee journey</p>
            </div>

            <!-- Form Progress Bar -->
            <div class="form-progress">
                <div class="form-progress-fill" id="progressFill"></div>
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

            <form method="POST" class="auth-form register-form" autocomplete="on" id="registerForm">
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i> Full Name <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required 
                        autocomplete="name"
                        placeholder="Enter your full name"
                        value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email <span class="required">*</span>
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
                    <label for="phone">
                        <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        autocomplete="tel"
                        placeholder="08xxxxxxxxxx"
                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password <span class="required">*</span>
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="new-password"
                            minlength="6"
                            placeholder="Minimum 6 characters">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Password Strength Bar -->
                    <div class="password-strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password <span class="required">*</span>
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required 
                            autocomplete="new-password"
                            placeholder="Repeat your password">
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="form-group terms-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="terms" name="terms" required>
                        <span>I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Account</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
                <p><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to home</a></p>
            </div>

            <!-- Benefits Section -->
            <div class="register-benefits">
                <h4><i class="fas fa-gift"></i> Member Benefits:</h4>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Faster checkout process</li>
                    <li><i class="fas fa-check-circle"></i> Track your orders</li>
                    <li><i class="fas fa-check-circle"></i> Earn loyalty points</li>
                    <li><i class="fas fa-check-circle"></i> Exclusive member promotions</li>
                </ul>
            </div>
        </div>

        <!-- Image Side -->
        <div class="auth-image">
            <div class="auth-image-overlay">
                <h2>Start Your Coffee Journey</h2>
                <p>Join thousands of coffee lovers and enjoy an exceptional shopping experience</p>
                <div class="register-stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <h3>10,000+</h3>
                        <p>Happy Members</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-coffee"></i>
                        <h3>50+</h3>
                        <p>Coffee Variants</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-star"></i>
                        <h3>4.9/5</h3>
                        <p>Customer Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    // Toggle Password Visibility
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.parentElement.querySelector('.toggle-password');
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Form Progress Bar
    function updateProgress() {
        const form = document.getElementById('registerForm');
        const inputs = form.querySelectorAll('input[required]');
        const filled = Array.from(inputs).filter(input => {
            if (input.type === 'checkbox') {
                return input.checked;
            }
            return input.value.trim() !== '';
        });
        
        const progress = (filled.length / inputs.length) * 100;
        document.getElementById('progressFill').style.width = progress + '%';
    }
    
    // Password Strength Indicator
    function checkPasswordStrength(password) {
        const strengthFill = document.getElementById('strengthFill');
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        strengthFill.className = 'strength-fill';
        
        if (strength <= 2) {
            strengthFill.style.width = '33%';
            strengthFill.style.background = '#d32f2f';
        } else if (strength <= 3) {
            strengthFill.style.width = '66%';
            strengthFill.style.background = '#ff9800';
        } else {
            strengthFill.style.width = '100%';
            strengthFill.style.background = '#4caf50';
        }
    }
    
    // Form Submission Handler
    let isSubmitting = false;
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        // Update progress on input
        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', updateProgress);
            input.addEventListener('change', updateProgress);
        });
        
        // Password strength checker
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
        
        // Password match validation
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Form submission
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }
            
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters!');
                return false;
            }
            
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> <span>Creating account...</span>';
        });
        
        // Auto-focus
        document.getElementById('full_name').focus();
        
        // Initial progress
        updateProgress();
    });
    </script>
</body>
</html>