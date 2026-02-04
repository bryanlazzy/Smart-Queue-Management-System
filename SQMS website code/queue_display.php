<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Queue Display | Smart Queue Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/display.css">
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
    <link rel="icon" href="/images/dlsud-logo.png">
</head>
<body>
    <!-- Auto-refresh indicator -->
    <div class="refresh-indicator">
        <i class="fas fa-sync-alt refresh-icon"></i>
        Live Updates
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
    </nav>


    <!-- Header Section -->
    <section class="header-section">
        <div class="container">
            <div class="header-content">
                <h1 class="header-title">
                    <i class="fas fa-tv me-3"></i>Live Queue Display
                </h1>
                <p class="header-subtitle">Real-time queue monitoring system (Top 10 per window)</p>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>System Online</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Queue Display Section -->
    <section class="queue-display-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 queue-column animate-on-scroll">
                    <div class="queue-card">
                        <div class="queue-header">
                            <div class="queue-title">
                                <i class="fas fa-calculator"></i>
                                Assessment Window
                            </div>
                            <p class="queue-subtitle">Counter 12</p>
                        </div>
                        <div class="queue-body">
                            <div id="window1" class="queue-list" data-prefix="Assessment">
                                <div class="no-queue">
                                    <i class="fas fa-hourglass-half no-queue-icon"></i>
                                    <div>Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 queue-column animate-on-scroll">
                    <div class="queue-card">
                        <div class="queue-header">
                            <div class="queue-title">
                                <i class="fas fa-edit"></i>
                                Posting Window
                            </div>
                            <p class="queue-subtitle">Counter 10</p>
                        </div>
                        <div class="queue-body">
                            <div id="window2" class="queue-list" data-prefix="Posting">
                                <div class="no-queue">
                                    <i class="fas fa-hourglass-half no-queue-icon"></i>
                                    <div>Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 queue-column animate-on-scroll">
                    <div class="queue-card">
                        <div class="queue-header">
                            <div class="queue-title">
                                <i class="fas fa-cash-register"></i>
                                Cashier Window
                            </div>
                            <p class="queue-subtitle">Counter 15 & 16</p>
                        </div>
                        <div class="queue-body">
                            <div id="window3" class="queue-list" data-prefix="Cashier">
                                <div class="no-queue">
                                    <i class="fas fa-hourglass-half no-queue-icon"></i>
                                    <div>Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 queue-column animate-on-scroll">
                    <div class="queue-card">
                        <div class="queue-header">
                            <div class="queue-title">
                                <i class="fas fa-cogs"></i>
                                Others
                            </div>
                            <p class="queue-subtitle">Other Services</p>
                        </div>
                        <div class="queue-body">
                            <div id="window4" class="queue-list" data-prefix="Others">
                                <div class="no-queue">
                                    <i class="fas fa-hourglass-half no-queue-icon"></i>
                                    <div>Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-2">&copy; 2025 Smart Queue Management System. All rights reserved.</p>
            <p class="text-muted small">Developed for Ayuntamiento - De La Salle University - Dasmariñas</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let previousQueues = {
            window1: [],
            window2: [],
            window3: [],
            window4: []
        };

        let firstLoad = true;

        let noQueueState = {
            window1: false,
            window2: false,
            window3: false,
            window4: false
        };

        function updateQueueDisplays() {
            fetch('fetch_queues.php')
                .then(response => response.json())
                .then(data => {
                    ['window1', 'window2', 'window3', 'window4'].forEach(id => {
                        // Limit to top 10 queues
                        const newList = data[id].slice(0, 10);
                        const container = document.getElementById(id);
                        const prefix = container.dataset.prefix;
                        const currentItems = Array.from(container.querySelectorAll('.queue-number'));
                        const currentNumbers = currentItems.map(el => el.dataset.queue);

                        // Remove "Loading..." on first load
                        if (firstLoad) {
                            container.innerHTML = '';
                        }

                        // Identify changes
                        const removed = currentNumbers.filter(n => !newList.includes(n));
                        const added = newList.filter(n => !currentNumbers.includes(n));

                        // Remove old items with fade-out
                        currentItems.forEach(el => {
                            if (removed.includes(el.dataset.queue)) {
                                el.classList.add('fade-out');
                                setTimeout(() => el.remove(), 500);
                            }
                        });

                        // Add new items with fade-in (always append to end)
                        newList.forEach((num, index) => {
                            if (!currentNumbers.includes(num)) {
                                const newEl = document.createElement('div');
                                newEl.className = 'queue-number fade-in';
                                newEl.dataset.queue = num;
                                newEl.innerHTML = `<i class="fas fa-ticket-alt me-2"></i>${prefix} #${num}`;
                                
                                // Mark first item as current
                                if (index === 0) {
                                    newEl.classList.add('current');
                                }
                                
                                // Always append new items to the end
                                container.appendChild(newEl);
                                setTimeout(() => newEl.classList.remove('fade-in'), 500);
                            }
                        });

                        // Reorder all items to match server order
                        setTimeout(() => {
                            const allItems = Array.from(container.querySelectorAll('.queue-number'));
                            // Sort items based on their position in newList
                            allItems.sort((a, b) => {
                                const aIndex = newList.indexOf(a.dataset.queue);
                                const bIndex = newList.indexOf(b.dataset.queue);
                                return aIndex - bIndex;
                            });
                            // Reappend in correct order
                            allItems.forEach(item => container.appendChild(item));
                        }, 100);

                        // Update current queue highlighting (after reordering completes)
                        setTimeout(() => {
                            const queueItems = container.querySelectorAll('.queue-number');
                            queueItems.forEach((el, index) => {
                                if (index === 0) {
                                    el.classList.add('current');
                                } else {
                                    el.classList.remove('current');
                                }
                            });
                        }, 200);

                        // Handle "No queue" ONLY IF needed (after all updates complete)
                        setTimeout(() => {
                            const hasQueueItems = container.querySelectorAll('.queue-number').length > 0;
                            const noQueueEl = container.querySelector('.no-queue');

                            if (newList.length === 0 && !hasQueueItems) {
                                if (!noQueueEl) {
                                    const noQ = document.createElement('div');
                                    noQ.className = 'no-queue';
                                    noQ.innerHTML = `
                                        <i class="fas fa-clipboard-list no-queue-icon"></i>
                                        <div>No queue at the moment</div>
                                    `;
                                    container.appendChild(noQ);
                                    noQueueState[id] = true;
                                }
                            } else {
                                if (noQueueEl) noQueueEl.remove();
                                noQueueState[id] = false;
                            }
                        }, 520);
                    });

                    firstLoad = false;
                })
                .catch(err => {
                    console.error("Error fetching queue data:", err);
                    // Show error state
                    ['window1', 'window2', 'window3', 'window4'].forEach(id => {
                        const container = document.getElementById(id);
                        if (container.children.length === 0) {
                            container.innerHTML = `
                                <div class="no-queue">
                                    <i class="fas fa-exclamation-triangle no-queue-icon text-warning"></i>
                                    <div>Connection error. Retrying...</div>
                                </div>
                            `;
                        }
                    });
                });
        }

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

        // Initialize
        updateQueueDisplays();
        setInterval(updateQueueDisplays, 2000);

        // Add some interactive feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            setTimeout(() => {
                document.querySelectorAll('.queue-card').forEach((card, index) => {
                    setTimeout(() => {
                        card.style.transform = 'translateY(0)';
                        card.style.opacity = '1';
                    }, index * 200);
                });
            }, 500);
        });
    </script>
</body>
</html>