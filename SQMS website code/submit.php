<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Queue Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/submitstyle.css">
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

<!-- Fullscreen Loading Screen -->
<div id="loader-overlay">
  <div class="loader-spinner"></div>
  <div class="loader-text">Processing your request...</div>
</div>

<?php
session_start();
require 'config.php';

// Helper function to show error and hide loader
function showErrorAndExit($message) {
    echo $message;
    echo "<script>
        document.getElementById('loader-overlay').style.opacity = '0';
        setTimeout(() => {
            document.getElementById('loader-overlay').style.display = 'none';
        }, 300);
    </script>";
    echo "</body></html>";
    exit();
}

// Check if admin is logged in for this service
if (!isset($_SESSION['admin'])) {
    showErrorAndExit("<div class='container-result error'>
        <h3>Error</h3>
        <p>Admin must be logged in to accept registrations for this service.</p>
        <a href='admin_login.php'>Admin Login</a>
    </div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $contactnumber = isset($_POST['contactnumber']) ? trim($_POST['contactnumber']) : '';
    $service = isset($_POST['service']) ? trim($_POST['service']) : '';

    // Server-side validation
    if (empty($name) || empty($contactnumber) || empty($service)) {
        showErrorAndExit("<div class='container-result error'><h3>Error</h3><p>Please fill all fields.</p><a href='registration.php'>Back to form</a></div>");
    }

    if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        showErrorAndExit("<div class='container-result error'><h3>Error</h3><p>Invalid name format.</p><a href='registration.php'>Back to form</a></div>");
    }

    if (!preg_match("/^09[0-9]{9}$/", $contactnumber)) {
        showErrorAndExit("<div class='container-result error'><h3>Error</h3><p>Invalid mobile number format. Must start with 09 and be exactly 11 digits.</p><a href='registration.php'>Back to form</a></div>");
    }

    // Map services to corresponding table names
    $service_table_map = [
        "Assessment Window" => "assessment_window",
        "Posting and Unholding of Account" => "posting_unholding_account",
        "Cashier Window" => "cashier_service",
        "Others" => "other_service"
    ];

    if (!array_key_exists($service, $service_table_map)) {
        showErrorAndExit("<div class='container-result error'><h3>Error</h3><p>Invalid service selected.</p><a href='registration.php'>Back to form</a></div>");
    }

    $table = $service_table_map[$service];
    
    // Check if logged in admin is registration admin OR matches the service
    $isRegistrationAdmin = ($_SESSION['admin']['service_table'] === 'all_services');
    
    if (!$isRegistrationAdmin && $_SESSION['admin']['service_table'] !== $table) {
        showErrorAndExit("<div class='container-result error'>
            <h3>Error</h3>
            <p>Service mismatch detected. This should not happen.</p>
            <p>The logged-in admin does not manage this service window.</p>
            <p>Please logout and login with the correct admin account for <strong>$service</strong>.</p>
            <a href='logout.php'>Logout</a>
            <a href='registration.php'>Back to Form</a>
        </div>");
    }

    // Create tracking table if it doesn't exist
    $createTrackingTable = "CREATE TABLE IF NOT EXISTS user_queue_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contactnumber VARCHAR(20) NOT NULL,
        service_table VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_contact_service (contactnumber, service_table)
    )";
    $conn->query($createTrackingTable);

    // Check if this user already has an active queue in this service
    $checkDuplicate = "SELECT * FROM user_queue_tracking WHERE contactnumber = ? AND service_table = ?";
    $checkStmt = $conn->prepare($checkDuplicate);
    $checkStmt->bind_param("ss", $contactnumber, $table);
    $checkStmt->execute();
    $duplicateResult = $checkStmt->get_result();

    if ($duplicateResult->num_rows > 0) {
        $checkStmt->close();
        showErrorAndExit("<div class='container-result error'>
            <h3>Duplicate Registration</h3>
            <p>You already have an active queue for <strong>$service</strong>.</p>
            <p>Contact Number: <strong>$contactnumber</strong></p>
            <p>You can only register once per service window. Please wait for your current queue to be served.</p>
            <p><small>Note: You can register for other service windows.</small></p>
            <a href='registration.php'>Back to Form</a>
        </div>");
    }
    $checkStmt->close();

    // Insert data into the correct table with sms_sent = 0 (pending)
    $stmt = $conn->prepare("INSERT INTO `$table` (name, contactnumber, sms_sent, sms_registered_sent, sms_near_sent, sms_next_sent, current_serving, sms_sent_time) VALUES (?, ?, 0, 0, 0, 0, 0, NULL)");
    $stmt->bind_param("ss", $name, $contactnumber);

    if ($stmt->execute()) {
        $queueNumber = $stmt->insert_id;
        
        // Add to tracking table
        $insertTracking = "INSERT INTO user_queue_tracking (contactnumber, service_table) VALUES (?, ?)";
        $trackStmt = $conn->prepare($insertTracking);
        $trackStmt->bind_param("ss", $contactnumber, $table);
        $trackStmt->execute();
        $trackStmt->close();
        
        // IMPORTANT: Check if this is the FIRST entry in the table
        // If yes, mark it as currently serving
        $countSql = "SELECT COUNT(*) as total FROM `$table`";
        $countResult = $conn->query($countSql);
        $countRow = $countResult->fetch_assoc();
        
        if ($countRow['total'] == 1) {
            // This is the first entry, mark it as currently serving
            $updateSql = "UPDATE `$table` SET current_serving = 1 WHERE queuenumber = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $queueNumber);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // Check if ANY entry has current_serving = 1
            $checkServingSql = "SELECT COUNT(*) as serving_count FROM `$table` WHERE current_serving = 1";
            $checkServingResult = $conn->query($checkServingSql);
            $checkServingRow = $checkServingResult->fetch_assoc();
            
            // If no entry is marked as currently serving, mark the first one
            if ($checkServingRow['serving_count'] == 0) {
                $setFirstServingSql = "UPDATE `$table` SET current_serving = 1 ORDER BY queuenumber ASC LIMIT 1";
                $conn->query($setFirstServingSql);
            }
        }
        
        // Generate queue display link
        $queueDisplayUrl = "https://sqms.online/queue_display.php";
        
        echo "
            <div class='container-result success'>
                <div class='success-icon'>✓</div>
                <h3>Registration Successful!</h3>
                <div class='queue-display'>
                  <h1>  <div class='queue-label'>Your Queue Number</div> </h1>
                   <h1> <div class='queue-number'>#{$queueNumber}</div> </h1>
                </div>
                <div class='service-info'>
                    <p><strong>Service:</strong> $service</p>
                    <p><strong>Name:</strong> $name</p>
                    <p><strong>Contact:</strong> $contactnumber</p>
                </div>
                <div class='sms-notification'>
                    <i>📱 An SMS notification will be sent to your mobile number shortly with a link to view the live queue display.</i>
                </div>
                <div class='instructions'>
                    <p>Please keep your queue number and wait for your turn.</p>
                    <p>You can monitor the live queue display at:</p>
                    <p><a href='$queueDisplayUrl' target='_blank' style='color: var(white); font-weight: bold;'>$queueDisplayUrl</a></p>
                </div>
                <a href='registration.php' class='btn-back'>Register Another</a>
                <a href='index.php' class='btn-home'>Back to Home</a>
            </div>
        ";

    } else {
        showErrorAndExit("<div class='container-result error'><h3>Error</h3><p>Database error: " . $stmt->error . "</p><a href='registration.php'>Back to form</a></div>");
    }

    $stmt->close();
    $conn->close();
} else {
    showErrorAndExit("<div class='container-result error'><h3>Error</h3><p>Invalid request method.</p><a href='registration.php'>Back to form</a></div>");
}
?>

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
      if (href && !href.startsWith("#") && !href.startsWith("javascript:")) {
        document.getElementById("loader-overlay").style.display = "flex";
        document.getElementById("loader-overlay").style.opacity = "1";
      }
    });
  });
</script>

</body>
</html>