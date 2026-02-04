<?php
session_start();
require 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$admin = $_SESSION['admin'];
$table = $admin['service_table'];

// Handle AJAX requests for auto-refresh
if (isset($_GET['ajax']) && $_GET['ajax'] == 'refresh') {
    header('Content-Type: application/json');
    
    // Get current queue
    $current = $conn->query("SELECT * FROM `$table` ORDER BY queuenumber ASC LIMIT 1")->fetch_assoc();
    
    // Get full queue
    $queue = $conn->query("SELECT * FROM `$table` ORDER BY queuenumber ASC");
    $queueData = [];
    while ($row = $queue->fetch_assoc()) {
        $queueData[] = $row;
    }
    
    // Get total count
    $totalCount = $conn->query("SELECT COUNT(*) as total FROM `$table`")->fetch_assoc()['total'];
    
    echo json_encode([
        'current' => $current,
        'queue' => $queueData,
        'total' => $totalCount
    ]);
    exit();
}

// "Next" button - Move to next queue and clear user's registration from tracking table
if (isset($_POST['next'])) {
    // Get the current first queue
    $currentQueue = $conn->query("SELECT queuenumber, contactnumber FROM `$table` ORDER BY queuenumber ASC LIMIT 1")->fetch_assoc();
    
    if ($currentQueue) {
        $queueNum = $currentQueue['queuenumber'];
        $contactNumber = $currentQueue['contactnumber'];
        
        // Remove this user's contact from the tracking table for this service
        $deleteTrackingSql = "DELETE FROM user_queue_tracking WHERE contactnumber = ? AND service_table = ?";
        $deleteStmt = $conn->prepare($deleteTrackingSql);
        $deleteStmt->bind_param("ss", $contactNumber, $table);
        $deleteStmt->execute();
        $deleteStmt->close();
        
        // Delete the current queue
        $conn->query("DELETE FROM `$table` WHERE queuenumber = $queueNum");
        
        // Get the NEW first queue (after deletion)
        $newCurrent = $conn->query("SELECT queuenumber FROM `$table` ORDER BY queuenumber ASC LIMIT 1")->fetch_assoc();
        
        if ($newCurrent) {
            // Clear all current_serving flags
            $conn->query("UPDATE `$table` SET current_serving = 0");
            
            // Set new current_serving
            $conn->query("UPDATE `$table` SET current_serving = 1 WHERE queuenumber = " . $newCurrent['queuenumber']);
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// "Reset" button - CLEAR ALL QUEUES AND RESET tracking table
if (isset($_POST['reset'])) {
    // Remove all users from tracking table for this service
    $deleteAllTrackingSql = "DELETE FROM user_queue_tracking WHERE service_table = ?";
    $deleteStmt = $conn->prepare($deleteAllTrackingSql);
    $deleteStmt->bind_param("s", $table);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    // Complete reset (deletes all queue data)
    $conn->query("TRUNCATE TABLE `$table`");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Initial data load
$current = $conn->query("SELECT * FROM `$table` ORDER BY queuenumber ASC LIMIT 1")->fetch_assoc();

// Set current_serving flag for the first queue if not already set
if ($current) {
    $checkServing = $conn->query("SELECT COUNT(*) as count FROM `$table` WHERE current_serving = 1")->fetch_assoc();
    if ($checkServing['count'] == 0) {
        $conn->query("UPDATE `$table` SET current_serving = 0");
        $conn->query("UPDATE `$table` SET current_serving = 1 WHERE queuenumber = " . $current['queuenumber']);
    }
}

$queue = $conn->query("SELECT * FROM `$table` ORDER BY queuenumber ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Queue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="styles/admindashboard.css" rel="stylesheet">
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
    
<div class="refresh-indicator" id="refreshIndicator">
    <i class="fas fa-sync-alt fa-spin me-2"></i>Updating...
</div>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand fs-5 fw-bold text-wrap" href="index.php">
            <i class="fas fa-project-diagram me-2"></i>Smart Queue Management System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navItems">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navItems">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="registration.php">
                        <i class="fas fa-plus-circle me-1"></i> Register to Queue
                    </a>
                </li>
               <li class="nav-item">
                    <a class="nav-link logout-link" href="logout.php"> <i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <div class="card shadow-lg border-0 mb-4">
        <div class="card-header text-white text-center py-3">
            <h2 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
            <p class="lead mb-0">Manage your queue efficiently with SMS notifications.</p>
        </div>
        <div class="card-body p-4">
            <h3 class="card-title mb-3">Welcome, <strong><?php echo htmlspecialchars($admin['username']); ?></strong></h3>
            <h5 class="mb-4">Monitoring Queue for: <strong class="text-primary-green"><?php echo htmlspecialchars($table); ?></strong></h5>

            <!-- Auto-refresh controls -->
            <div class="auto-refresh-controls">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-2">
                            <span class="status-indicator status-active" id="statusIndicator"></span>
                            Auto-refresh: <span id="refreshStatus">Active</span>
                        </h6>
                        <small class="text-muted">Updates every <span id="intervalDisplay">3</span> seconds</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-sm btn-outline-success me-2" id="toggleRefresh">
                            <i class="fas fa-pause" id="toggleIcon"></i> Pause
                        </button>
                        <button class="btn btn-sm btn-outline-primary" id="manualRefresh">
                            <i class="fas fa-sync-alt"></i> Refresh Now
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-4 p-3 bg-light border rounded shadow-sm current-queue-box" id="currentQueueBox">
                <?php if ($current): ?>
                    <h4 class="text-primary-green mb-2"><i class="fas fa-user-friends me-2"></i>Current Queue (Now Serving): <span class="display-6 fw-bold">#<?php echo $current['queuenumber']; ?></span></h4>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($current['name']); ?><br>
                    <strong>Contact:</strong> <?php echo htmlspecialchars($current['contactnumber']); ?></p>
                <?php else: ?>
                    <h4 class="text-secondary"><i class="fas fa-info-circle me-2"></i>No users currently in queue.</h4>
                <?php endif; ?>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <form method="POST" class="w-100">
                        <button type="submit" name="next" class="btn btn-success btn-lg w-100" <?php echo !$current ? 'disabled' : ''; ?>>
                            <i class="fas fa-arrow-right me-2"></i>Next
                        </button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="POST" class="w-100" onsubmit="return confirm('Are you sure you want to reset the entire queue? This cannot be undone.')">
                        <button type="submit" name="reset" class="btn btn-danger btn-lg w-100">
                            <i class="fas fa-redo me-2"></i>Reset Queue
                        </button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary-green text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Queue List 
                        <span class="badge bg-light text-dark float-end" id="totalCount"></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                <table class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Queue #</th>
                            <th>Name</th>
                            <th>Contact Number</th>
                            <th>SMS Status</th>
                        </tr>
                    </thead>
                    <tbody id="queueTableBody">
                        <?php if ($queue->num_rows > 0): ?>
                            <?php while ($row = $queue->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['queuenumber']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contactnumber']); ?></td>
                                    <td>
                                        <?php if ($row['sms_registered_sent']): ?>
                                            <span class="badge bg-success">Sent ✓</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No entries in queue.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="footer mt-5 py-3 bg-dark text-white-50">
    <div class="container text-center">
        <span>&copy; <?php echo date("Y"); ?> Smart Queue Management System. All rights reserved.</span>
        <p class="small">Developed for Ayuntamiento - De La Salle University - Dasmariñas</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-refresh functionality
let refreshInterval;
let isRefreshing = true;
let refreshIntervalTime = 3000;

const refreshIndicator = document.getElementById('refreshIndicator');
const currentQueueBox = document.getElementById('currentQueueBox');
const queueTableBody = document.getElementById('queueTableBody');
const totalCount = document.getElementById('totalCount');
const toggleRefreshBtn = document.getElementById('toggleRefresh');
const manualRefreshBtn = document.getElementById('manualRefresh');
const refreshStatus = document.getElementById('refreshStatus');
const statusIndicator = document.getElementById('statusIndicator');

function startAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    refreshInterval = setInterval(refreshQueue, refreshIntervalTime);
    isRefreshing = true;
    updateRefreshStatus();
}

function stopAutoRefresh() {
    if (refreshInterval) clearInterval(refreshInterval);
    isRefreshing = false;
    updateRefreshStatus();
}

function updateRefreshStatus() {
    if (isRefreshing) {
        refreshStatus.textContent = 'Active';
        statusIndicator.className = 'status-indicator status-active';
        toggleRefreshBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
    } else {
        refreshStatus.textContent = 'Paused';
        statusIndicator.className = 'status-indicator status-paused';
        toggleRefreshBtn.innerHTML = '<i class="fas fa-play"></i> Resume';
    }
}

function showRefreshIndicator() {
    refreshIndicator.classList.add('show');
    setTimeout(() => refreshIndicator.classList.remove('show'), 1000);
}

function refreshQueue() {
    showRefreshIndicator();
    fetch(window.location.href + '?ajax=refresh')
        .then(response => response.json())
        .then(data => {
            updateCurrentQueue(data.current);
            updateQueueTable(data.queue);
            updateTotalCount(data.total);
        })
        .catch(error => console.error('Error:', error));
}

function updateCurrentQueue(current) {
    if (current) {
        currentQueueBox.innerHTML = `
            <h4 class="text-primary-green mb-2">
                <i class="fas fa-user-friends me-2"></i>Current Queue (Now Serving): 
                <span class="display-6 fw-bold">#${current.queuenumber}</span>
            </h4>
            <p class="mb-1">
                <strong>Name:</strong> ${escapeHtml(current.name)}<br>
                <strong>Contact:</strong> ${escapeHtml(current.contactnumber)}
            </p>
        `;
    } else {
        currentQueueBox.innerHTML = `
            <h4 class="text-secondary">
                <i class="fas fa-info-circle me-2"></i>No users currently in queue.
            </h4>
        `;
    }
    currentQueueBox.classList.add('pulse-animation');
    setTimeout(() => currentQueueBox.classList.remove('pulse-animation'), 1000);
}

function updateQueueTable(queue) {
    if (queue.length > 0) {
        let tableHTML = '';
        queue.forEach(row => {
            let smsStatus = '';
            if (row.sms_registered_sent) {
                smsStatus = '<span class="badge bg-success">Sent ✓</span>';
            } else {
                smsStatus = '<span class="badge bg-warning text-dark">Pending</span>';
            }
            
            tableHTML += `
                <tr>
                    <td>#${row.queuenumber}</td>
                    <td>${escapeHtml(row.name)}</td>
                    <td>${escapeHtml(row.contactnumber)}</td>
                    <td>${smsStatus}</td>
                </tr>
            `;
        });
        queueTableBody.innerHTML = tableHTML;
    } else {
        queueTableBody.innerHTML = '<tr><td colspan="4" class="text-center">No entries in queue.</td></tr>';
    }
}

function updateTotalCount(count) {
    totalCount.textContent = count > 0 ? count : '';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

toggleRefreshBtn.addEventListener('click', () => {
    isRefreshing ? stopAutoRefresh() : startAutoRefresh();
});

manualRefreshBtn.addEventListener('click', refreshQueue);

window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    navbar.style.background = window.scrollY > 50 
        ? 'var(--dark-green-gradient)' 
        : 'var(--dark-green-gradient)';
});

document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
    const initialRows = queueTableBody.querySelectorAll('tr');
    const initialCount = initialRows.length > 0 && !initialRows[0].querySelector('td[colspan]') ? initialRows.length : 0;
    updateTotalCount(initialCount);
});

document.addEventListener('visibilitychange', function() {
    document.hidden ? stopAutoRefresh() : (isRefreshing && startAutoRefresh());
});
</script>
</body>
</html>