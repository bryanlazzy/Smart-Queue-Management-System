<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['queuenumber']) && isset($_POST['notification_type']) && isset($_POST['table'])) {
        $queueNumber = intval($_POST['queuenumber']);
        $notificationType = trim($_POST['notification_type']);
        $table = trim($_POST['table']);
        
        // Validate notification type
        $validTypes = ['registered', 'near', 'next'];
        if (!in_array($notificationType, $validTypes)) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid notification type"
            ]);
            exit();
        }
        
        // Whitelist the table name to prevent SQL injection
        $validTables = ['assessment_window', 'posting_unholding_account', 'cashier_service', 'other_service'];
        if (!in_array($table, $validTables)) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid table name"
            ]);
            exit();
        }
        
        // Determine which column to update
        $columnMap = [
            'registered' => 'sms_registered_sent',
            'near' => 'sms_near_sent',
            'next' => 'sms_next_sent'
        ];
        
        $column = $columnMap[$notificationType];
        
        // Update the specific row in the specified table directly
        $updateSql = "UPDATE `$table` SET `$column` = 1, sms_sent_time = NOW() WHERE queuenumber = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $queueNumber);
        
        if ($updateStmt->execute()) {
            if ($updateStmt->affected_rows > 0) {
                echo json_encode([
                    "success" => true, 
                    "message" => "Marked $notificationType notification as sent",
                    "queuenumber" => $queueNumber,
                    "notification_type" => $notificationType,
                    "table" => $table
                ]);
            } else {
                echo json_encode([
                    "success" => false, 
                    "message" => "Queue number $queueNumber not found in table $table",
                    "queuenumber" => $queueNumber
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Update query failed: " . $updateStmt->error
            ]);
        }
        $updateStmt->close();
        
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Missing required parameters (queuenumber, notification_type, and table)"
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}

$conn->close();
?>