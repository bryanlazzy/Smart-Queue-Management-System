<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | Smart Queue Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/191cebb872.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary-green: #00743F;
            --light-green: #67C589;
            --accent-green: #B8E6C4;
            --bg-light: #F8FAF9;
            --text-dark: #2C3E50;
            --shadow-light: rgba(0, 116, 63, 0.1);
            --shadow-medium: rgba(0, 116, 63, 0.2);
        }
    </style>
    <link href="styles/indexstyle.css" rel="stylesheet">
    <link rel="icon" href="/images/dlsud-logo.png">
</head>
<body>

<!-- Loading Screen -->
<div id="loader-overlay">
  <div class="loader-spinner"></div>
  <div class="loader-text">Loading Smart Queue Management System...</div>
</div>

<!-- Navbar -->
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
                    <a class="nav-link" href="#about-section">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="queue_display.php">View Live Queue</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="registration.php">Register to Queue</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_login.php">Admin Login</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center justify-content-center text-center text-md-start">
      <div class="col-md-7 hero-content">
        <h1>Welcome to the Smart Queue Management System</h1>
        <p>Your time matters. Enjoy quicker transactions and less waiting at the Ayuntamiento with smart, real-time queue notifications.</p>
        <p class="text-muted small mb-3">
          <i class="fas fa-info-circle me-1"></i>
          Note: Registration admin or service window admin must be logged in to accept registrations
        </p>
        <a href="registration.php" class="cta-button">
          <i class="fas fa-ticket-alt me-2"></i>Get Your Queue Number
        </a>
      </div>
      <div class="col-md-5 text-center">
        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIiByeD0iMjAiIGZpbGw9InVybCgjZ3JhZGllbnQwX2xpbmVhcl8xXzEpIi8+CjxwYXRoIGQ9Ik0xMDAgNTBMMTIwIDkwSDgwTDEwMCA1MFoiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjkiLz4KPGNpcmNsZSBjeD0iMTAwIiBjeT0iMTMwIiByPSIzMCIgZmlsbD0id2hpdGUiIG9wYWNpdHk9IjAuOCIvPgo8dGV4dCB4PSIxMDAiIHk9IjE0MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjI0IiBmb250LXdlaWdodD0iYm9sZCIgZmlsbD0iIzAwNzQzRiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+UTwvdGV4dD4KPHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxkZWZzPgo8bGluZWFyR3JhZGllbnQgaWQ9ImdyYWRpZW50MF9saW5lYXJfMV8xIiB4MT0iMCIgeTE9IjAiIHgyPSIyMDAiIHkyPSIyMDAiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIj4KPHN0b3Agc3RvcC1jb2xvcj0iIzY3QzU4OSIvPgo8c3RvcCBvZmZzZXQ9IjEiIHN0b3AtY29sb3I9IiNCOEU2QzQiLz4KPC9saW5lYXJHcmFkaWVudD4KPC9kZWZzPgo8L3N2Zz4KPC9zdmc+" alt="Queue Management Icon" class="hero-logo"/>
      </div>
    </div>
  </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
  <div class="container">
    <div class="row">
      <div class="col-md-4 stat-item animate-on-scroll">
        <span class="stat-number">0</span>
        <div class="stat-label">Students Served</div>
      </div>
      <div class="col-md-4 stat-item animate-on-scroll">
        <span class="stat-number">0min</span>
        <div class="stat-label">Average Wait Time</div>
      </div>
      <div class="col-md-4 stat-item animate-on-scroll">
        <span class="stat-number">0%</span>
        <div class="stat-label">User Satisfaction</div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section id="features">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-5 fw-bold mb-3 animate-on-scroll" style="color: #005a32;">Why Choose Our System?</h2>
        <p class="lead text-muted animate-on-scroll">Experience the future of queue management with our innovative features</p>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-4 animate-on-scroll">
        <div class="feature-col">
          <i class="fa-solid fa-circle-check fa-4x fa-icon"></i>
          <h3 class="feature-title">Streamlined Queue Management</h3>
          <p>Our system simplifies the entire queueing process by allowing users to register quickly and automatically placing them in the correct service queue. With real-time updates and organized request handling, transactions at the Ayuntamiento will become faster, smoother, and more convenient.</p>
        </div>
      </div>
      <div class="col-lg-4 animate-on-scroll">
        <div class="feature-col">
          <i class="fa-solid fa-shield-halved fa-4x fa-icon"></i>
          <h3 class="feature-title">Role-Based Admin Access</h3>
          <p> Each service window is equipped with a secure, dedicated admin portal. Staff can easily view and manage the queues in real time—ensuring efficient service delivery and full control over daily operations.</p>
        </div>
      </div>
      <div class="col-lg-4 animate-on-scroll">
        <div class="feature-col">
          <i class="fa-solid fa-mobile-screen-button fa-4x fa-icon"></i>
          <h3 class="feature-title">Mobile-Responsive Design</h3>
          <p>The platform is designed to work seamlessly across all devices. Whether on a tablet, phone, or computer, users can register effortlessly, and staff can manage queues anytime—ensuring accessibility and ease of use for everyone.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About Us Section -->
<section class="about-section" id="about-section">
  <div class="container about-content">
    <h2 class="text-center text-white mb-5 display-5 fw-bold animate-on-scroll">About Us</h2>
    <div class="row align-items-center">
      <div class="col-12 col-md-4 text-center mb-4 mb-md-0 animate-on-scroll">
        <div class="about-img" style="width: 140px; height: 140px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
          <i class="fas fa-university fa-4x text-white"></i>
        </div>
      </div>
      <div class="col-12 col-md-4 animate-on-scroll">
        <p class="lead text-white text-center text-md-start">
          Our innovative web-based queueing system is designed to transform how students and staff manage their time and access essential services at De La Salle University - Dasmariñas. By embracing a digital-first approach, the system delivers a faster, more transparent, and more efficient service experience—significantly reducing physical waiting times and creating a smoother flow for everyone on campus.
        </p>
      </div>
      <div class="col-12 col-md-4 text-center mb-3 mb-md-0 animate-on-scroll">
        <div class="about-img" style="width: 140px; height: 140px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
          <i class="fas fa-users fa-4x text-white"></i>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="text-center py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p class="mb-2">&copy; 2025 Smart Queue Management System. All rights reserved.</p>
                <p class="text-muted small">Developed for Ayuntamiento - De La Salle University - Dasmariñas</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
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

  // Scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
      }
    });
  }, observerOptions);

  // Observe all elements with animate-on-scroll class
  document.querySelectorAll('.animate-on-scroll').forEach(el => {
    observer.observe(el);
  });

  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
      navbar.style.background = 'linear-gradient(135deg, rgba(0, 116, 63, 0.95) 0%, rgba(0, 90, 50, 0.95) 100%)';
      navbar.style.backdropFilter = 'blur(15px)';
    } else {
      navbar.style.background = 'linear-gradient(135deg, var(--primary-green) 0%, #005a32 100%)';
      navbar.style.backdropFilter = 'blur(10px)';
    }
  });

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        const headerOffset = 80;
        const elementPosition = target.offsetTop;
        const offsetPosition = elementPosition - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  // Add some interactive elements
  document.querySelectorAll('.feature-col').forEach(col => {
    col.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    col.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
  });
</script>

</body>
</html>