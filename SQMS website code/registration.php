<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Queue Management System</title>
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

<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    ?>
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
                    <li class="nav-item">
                        <a class="nav-link" href="admin_login.php">
                            <i class="fas fa-user-shield me-1"></i> Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="card shadow-lg border-0">
            <div class="card-header text-white text-center py-4" style="background: var(--dark-green-gradient);">
                <h2 class="mb-0"><i class="fas fa-lock me-2"></i>Admin Login Required</h2>
                <p class="lead mb-0">Service window admin must be logged in to accept registrations</p>
            </div>
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-user-shield fa-5x text-muted"></i>
                </div>
                <h3 class="mb-4">Registration Temporarily Unavailable</h3>
                <p class="lead text-muted mb-4">
                    Before users can register for a queue, the admin for registration or admin for specific service window must be logged in to the system.
                </p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>For Registration Admin or Window Admins:</strong> Please login to enable registrations for your service window.
                </div>
                <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                    <a href="admin_login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
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
        // loader with delay
        window.addEventListener("load", function () {
          setTimeout(() => {
            document.getElementById("loader-overlay").style.opacity = "0";
            setTimeout(() => {
              document.getElementById("loader-overlay").style.display = "none";
            }, 300);
          }, 1000);
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
    </script>

    </body>
    </html>
    <?php
    exit();
}

// If admin is logged in, get service information
$adminServiceTable = $_SESSION['admin']['service_table'];
$adminUsername = $_SESSION['admin']['username'];

// Map service tables to service names
$service_name_map = [
    "assessment_window" => "Assessment Window",
    "posting_unholding_account" => "Posting and Unholding of Account",
    "cashier_service" => "Cashier Window",
    "other_service" => "Others"
];

// Check if this is the registration admin (can access all services)
$isRegistrationAdmin = ($adminServiceTable === 'all_services');

if ($isRegistrationAdmin) {
    $currentService = "All Services";
    $availableServices = $service_name_map;
} else {
    $currentService = isset($service_name_map[$adminServiceTable]) ? $service_name_map[$adminServiceTable] : "Unknown Service";
    $availableServices = [$currentService => $currentService];
}
?>

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
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-user-circle me-1"></i> Admin: <?php echo htmlspecialchars($adminUsername); ?>
                    </span>
                </li>
                <?php if (!$isRegistrationAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <div class="card shadow-lg border-0">
        <div class="card-header text-white text-center py-4" style="background: var(--dark-green-gradient);">
            <h2 class="mb-0">Register to Queue</h2>
            <p class="lead mb-0">Fill out the form below to get your queue number.</p>
        </div>
        
        <!-- Admin Service Alert -->
        <div class="alert alert-success mb-0 rounded-0 border-0">
            <div class="container">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Active Service:</strong> <?php echo htmlspecialchars($currentService); ?> 
                <span class="ms-2 text-muted">|</span>
                <span class="ms-2">Admin: <?php echo htmlspecialchars($adminUsername); ?></span>
            </div>
        </div>
        
        <div class="card-body p-4">
            <h3 class="card-title mb-4 text-center">SQMS Form</h3>
            
            <form action="submit.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required pattern="[A-Za-z ]{2,}" placeholder="e.g., Juan Dela Cruz">
                    <div class="invalid-feedback">
                        Please enter a valid full name (letters and spaces only, no numbers or special characters).
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>Letters and spaces only
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="contactnumber" class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="contactnumber" name="contactnumber" required pattern="09\d{9}" placeholder="e.g., 09123456789" maxlength="11">
                    <div class="invalid-feedback">
                        Please enter a valid 11-digit mobile number starting with 09.
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>Must start with 09 and be exactly 11 digits
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="service" class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                    <select name="service" class="form-select" id="service" required>
                        <option value="">-- Select Service --</option>
                        <?php foreach ($availableServices as $key => $serviceName): ?>
                            <option value="<?php echo htmlspecialchars($serviceName); ?>"><?php echo htmlspecialchars($serviceName); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php if ($isRegistrationAdmin): ?>
                            You can register for any service window
                        <?php else: ?>
                            Currently accepting registrations for: <strong><?php echo htmlspecialchars($currentService); ?></strong> only
                        <?php endif; ?>
                    </div>
                    <div class="invalid-feedback">
                        Please select a service type.
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Get Queue Number
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
    // loader with delay
    window.addEventListener("load", function () {
      setTimeout(() => {
        document.getElementById("loader-overlay").style.opacity = "0";
        setTimeout(() => {
          document.getElementById("loader-overlay").style.display = "none";
        }, 300);
      }, 1000);
    });

    // loader on link clicks
    document.querySelectorAll("a").forEach(link => {
      link.addEventListener("click", function (e) {
        const href = link.getAttribute("href");
        if (href && !href.startsWith("#") && !href.startsWith("javascript:")) {
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

    // Real-time validation for Full Name - only letters and spaces
    document.getElementById('name').addEventListener('input', function(e) {
        // Remove any non-letter characters except spaces
        this.value = this.value.replace(/[^A-Za-z ]/g, '');
    });

    // Real-time validation for Mobile Number - only numbers, must start with 09, max 11 digits
    document.getElementById('contactnumber').addEventListener('input', function(e) {
        // Remove any non-digit characters
        this.value = this.value.replace(/\D/g, '');
        
        // Limit to 11 digits
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
        
        // Auto-add 09 prefix if user starts typing a number that's not 0
        if (this.value.length === 1 && this.value !== '0') {
            this.value = '09' + this.value;
        }
        
        // Ensure it starts with 09
        if (this.value.length >= 2 && !this.value.startsWith('09')) {
            // If user typed something else, try to fix it
            if (this.value.startsWith('9')) {
                this.value = '0' + this.value;
            } else if (this.value.startsWith('0') && this.value[1] !== '9') {
                this.value = '09' + this.value.slice(1);
            }
        }
    });

    // Show immediate feedback on blur
    document.getElementById('contactnumber').addEventListener('blur', function(e) {
        if (this.value.length > 0 && this.value.length < 11) {
            this.setCustomValidity('Mobile number must be exactly 11 digits');
        } else if (this.value.length === 11 && !this.value.startsWith('09')) {
            this.setCustomValidity('Mobile number must start with 09');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

</body>
</html>