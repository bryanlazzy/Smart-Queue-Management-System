<?php
session_start();
require 'config.php'; // DB connection

// If already logged in, redirect
if (isset($_SESSION['admin'])) {
    // Redirect registrationadmin to registration page, others to dashboard
    if ($_SESSION['admin']['service_table'] === 'all_services') {
        header("Location: registration.php");
    } else {
        header("Location: admin_dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

// Handle forgot password form submission
if (isset($_POST['forgot_password'])) {
    $username = trim($_POST['forgot_username']);
    
    // Check if username exists
    $stmt = $conn->prepare("SELECT username FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Generate a reset token
        $reset_token = bin2hex(random_bytes(32));
        
        // Store the reset token in database using MySQL's DATE_ADD for consistent timezone
        $stmt = $conn->prepare("UPDATE admins SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE username = ?");
        $stmt->bind_param("ss", $reset_token, $username);
        
        if ($stmt->execute()) {
            // Verify the token was actually saved
            $verify_stmt = $conn->prepare("SELECT reset_token FROM admins WHERE username = ?");
            $verify_stmt->bind_param("s", $username);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 1) {
                $verify_row = $verify_result->fetch_assoc();
                if ($verify_row['reset_token'] === $reset_token) {
                    // Token successfully saved
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $script = $_SERVER['SCRIPT_NAME'];
                    $reset_link = $protocol . '://' . $host . $script . '?token=' . $reset_token;
                    $success = "Password reset link generated successfully! Click the button below or copy the link to reset your password.";
                    $show_reset_link = true;
                    $generated_reset_link = $reset_link;
                } else {
                    $error = "Error: Token mismatch after saving.";
                }
            } else {
                $error = "Error: Could not verify token was saved.";
            }
        } else {
            $error = "Error generating reset token. Please try again. Database error: " . $conn->error;
        }
    } else {
        $error = "Username not found.";
    }
}

// Handle password reset with token - ONLY when token is provided AND we're not processing a form submission
if (isset($_GET['token']) && !empty($_GET['token']) && !isset($_POST['forgot_password']) && !isset($_POST['reset_password'])) {
    $token = trim($_GET['token']);
    
    // Validate token format first
    if (strlen($token) !== 64 || !ctype_xdigit($token)) {
        $error = "Invalid token format.";
    } else {
        // Check token and expiration in one go
        $stmt = $conn->prepare("SELECT username, reset_token, reset_expires FROM admins WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Check if token exists but is expired
            $expired_check = $conn->prepare("SELECT username, reset_expires FROM admins WHERE reset_token = ?");
            $expired_check->bind_param("s", $token);
            $expired_check->execute();
            $expired_result = $expired_check->get_result();
            
            if ($expired_result->num_rows === 1) {
                $error = "Reset token has expired. Please request a new password reset.";
            } else {
                $error = "Reset token not found or has already been used.";
            }
        } else {
            $row = $result->fetch_assoc();
            // Token is valid and not expired
            $show_reset_form = true;
            $reset_username = $row['username'];
            $reset_token_from_db = $row['reset_token']; // Store for later verification
        }
    }
}

// Handle new password submission
if (isset($_POST['reset_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $username = trim($_POST['username']);
    $submitted_token = isset($_POST['token']) ? trim($_POST['token']) : '';
    
    if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Verify token is still valid before updating password
        $verify_stmt = $conn->prepare("SELECT username FROM admins WHERE username = ? AND reset_token = ? AND reset_expires > NOW()");
        $verify_stmt->bind_param("ss", $username, $submitted_token);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            $error = "Invalid or expired reset token. Please request a new password reset.";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password and clear reset token
            $stmt = $conn->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_expires = NULL WHERE username = ? AND reset_token = ?");
            $stmt->bind_param("sss", $hashed_password, $username, $submitted_token);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Password updated successfully! You can now login with your new password.";
                    $show_reset_form = false;
                    unset($show_reset_form); // Clear the reset form flag
                } else {
                    $error = "No account found with that username or token has been used.";
                }
            } else {
                $error = "Error updating password. Please try again. Database error: " . $conn->error;
            }
        }
    }
}

// Handle regular login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['forgot_password']) && !isset($_POST['reset_password'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin;
            
            // Redirect registrationadmin to registration page, others to dashboard
            if ($admin['service_table'] === 'all_services') {
                header("Location: registration.php");
            } else {
                header("Location: admin_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Smart Queue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="styles/style.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #00743F;
            --light-green: #67C589;
            --accent-green: #B8E6C4;
            --bg-light: #F8FAF9;
            --text-dark: #2C3E50;
            --shadow-light: rgba(0, 116, 63, 0.1);
            --shadow-medium: rgba(0, 116, 63, 0.2);
            --dark-green-gradient: linear-gradient(135deg, var(--primary-green) 0%, #005a32 100%);
        }
    </style>
    <link rel="icon" href="/images/dlsud-logo.png">
</head>
<body class="bg-light">

<div id="loader-overlay">
  <div class="loader-spinner"></div>
  <div class="loader-text">Loading Smart Queue Management System...</div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand fs-5 fw-bold text-wrap" href="index.php">
            <i class="fas fa-project-diagram me-2"></i>Smart Queue Management System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navItems"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navItems">
            <ul class="navbar-nav ms-auto">
                <?php if (basename($_SERVER['PHP_SELF']) === 'registration.php'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_login.php">
                            <i class="fas fa-user-shield me-1"></i> Admin Login
                        </a>
                    </li>
                <?php elseif (basename($_SERVER['PHP_SELF']) === 'admin_login.php'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="registration.php">
                            <i class="fas fa-arrow-left me-1"></i> Back to User Form
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5 d-flex justify-content-center align-items-center" style="min-height: calc(100vh - 120px);">
    <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%;">
        <div class="card-header text-white text-center py-4">
            <h2 class="mb-0">
                <i class="fas fa-lock me-2"></i>
                <?php echo isset($show_reset_form) && $show_reset_form ? 'Reset Password' : 'Admin Area Access'; ?>
            </h2>
            <p class="lead mb-0">
                <?php echo isset($show_reset_form) && $show_reset_form ? 'Enter your new password below.' : 'Please enter your credentials to proceed.'; ?>
            </p>
        </div>
        <div class="card-body p-4">
            <?php if (!empty($error)): ?>
                <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                    <?php if (isset($show_reset_link) && $show_reset_link): ?>
                        <div class="mt-3">
                            <div class="d-grid gap-2">
                                <a href="<?php echo htmlspecialchars($generated_reset_link); ?>" class="btn btn-success btn-sm">
                                    <i class="fas fa-key me-2"></i>Go to Password Reset
                                </a>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="copyResetLink('<?php echo htmlspecialchars($generated_reset_link); ?>')">
                                    <i class="fas fa-copy me-2"></i>Copy Reset Link
                                </button>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <strong>Reset Link:</strong> 
                                    <span class="text-break user-select-all" id="reset-link-text"><?php echo htmlspecialchars($generated_reset_link); ?></span>
                                </small>
                                <div class="mt-1">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This link will expire in 1 hour. Token: <?php echo substr($reset_token, 0, 8); ?>...
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($show_reset_form) && $show_reset_form): ?>
                <!-- Password Reset Form -->
                <h3 class="card-title mb-4 text-center">Reset Password</h3>
                <p class="text-muted text-center mb-4">Hi <strong><?php echo htmlspecialchars($reset_username); ?></strong>, please enter your new password.</p>
                
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($reset_username); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">New Password:</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required 
                               placeholder="Enter new password" minlength="6">
                        <div class="password-strength" id="password-strength"></div>
                        <div class="invalid-feedback">
                            Please enter a password (minimum 6 characters).
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required 
                               placeholder="Confirm new password">
                        <div class="invalid-feedback">
                            Please confirm your password.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" type="submit" name="reset_password">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                        <a href="admin_login.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Login
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <!-- Regular Login Form -->
                <h3 class="card-title mb-4 text-center">Admin Login</h3>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username:</label>
                        <input type="text" name="username" id="username" class="form-control" required 
                               placeholder="Enter your username">
                        <div class="invalid-feedback">
                            Please enter your username.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required 
                               placeholder="Enter your password">
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    </div>
                    
                    <div class="mb-4 text-end">
                        <a href="#" class="forgot-password-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            <i class="fas fa-key me-1"></i>Forgot Password?
                        </a>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" type="submit">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">
                    <i class="fas fa-key me-2"></i>Forgot Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <p class="text-muted">Enter your username to receive a password reset link.</p>
                    <div class="mb-3">
                        <label for="forgot_username" class="form-label fw-semibold">Username:</label>
                        <input type="text" name="forgot_username" id="forgot_username" class="form-control" required 
                               placeholder="Enter your username">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" name="forgot_password" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="footer mt-5 py-3 bg-dark text-white-50">
    <div class="container text-center">
        <span>&copy; <?php echo date("Y"); ?> Smart Queue Management System. All rights reserved.</span>
        <p class="text-muted small">Developed for Ayuntamiento - De La Salle University - Dasmariñas</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Enhanced loader with delay
    window.addEventListener("load", function () {
        setTimeout(() => {
            document.getElementById("loader-overlay").style.opacity = "0";
            setTimeout(() => {
                document.getElementById("loader-overlay").style.display = "none";
            }, 300);
        }, 1000);
    });

    // Show loader on link clicks
    document.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (href && !href.startsWith("#") && !href.startsWith("javascript:") && !href.startsWith("admin_login.php")) {
                document.getElementById("loader-overlay").style.display = "flex";
                document.getElementById("loader-overlay").style.opacity = "1";
            }
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.style.background = 'var(--dark-green-gradient)';
            navbar.style.backdropFilter = 'blur(15px)';
            navbar.style.boxShadow = '0 6px 25px var(--shadow-medium)';
        } else {
            navbar.style.background = 'var(--dark-green-gradient)';
            navbar.style.backdropFilter = 'blur(10px)';
            navbar.style.boxShadow = '0 4px 20px var(--shadow-light)';
        }
    });

    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('password-strength');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updateStrengthIndicator(strength);
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = newPasswordInput.value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    function checkPasswordStrength(password) {
        let score = 0;
        if (password.length >= 6) score++;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 3) return 'weak';
        if (score < 5) return 'medium';
        return 'strong';
    }

    function updateStrengthIndicator(strength) {
        if (!strengthIndicator) return;
        
        const messages = {
            weak: 'Weak password',
            medium: 'Medium strength',
            strong: 'Strong password'
        };
        
        strengthIndicator.textContent = messages[strength] || '';
        strengthIndicator.className = 'password-strength strength-' + strength;
    }

    // Bootstrap form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Auto-close alerts after 10 seconds (except reset link alerts)
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            // Don't auto-close alerts that contain reset links
            if (!alert.querySelector('.btn-success')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 10000);

    // Copy reset link function
    function copyResetLink(link) {
        navigator.clipboard.writeText(link).then(function() {
            // Show temporary success message
            const copyBtn = event.target.closest('button');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
            copyBtn.classList.remove('btn-outline-success');
            copyBtn.classList.add('btn-success');
            
            setTimeout(function() {
                copyBtn.innerHTML = originalText;
                copyBtn.classList.remove('btn-success');
                copyBtn.classList.add('btn-outline-success');
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = link;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            const copyBtn = event.target.closest('button');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
            copyBtn.classList.remove('btn-outline-success');
            copyBtn.classList.add('btn-success');
            
            setTimeout(function() {
                copyBtn.innerHTML = originalText;
                copyBtn.classList.remove('btn-success');
                copyBtn.classList.add('btn-outline-success');
            }, 2000);
        });
    }
</script>

</body>
</html>