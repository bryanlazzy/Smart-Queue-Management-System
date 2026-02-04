<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'config.php';

try {
    $service = isset($_GET['service']) ? trim($_GET['service']) : '';
    
    // Map services to table names
    $service_table_map = [
        "Assessment Window" => "assessment_window",
        "Posting and Unholding of Account" => "posting_unholding_account",
        "Cashier Window" => "cashier_service",
        "Others" => "other_service"
    ];
    
    if (empty($service) || !array_key_exists($service, $service_table_map)) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid service',
            'current_queue' => 0
        ]);
        exit();
    }
    
    $table = $service_table_map[$service];
    
    // ALWAYS get the first (topmost) queue number
    $sql = "SELECT queuenumber FROM `$table` ORDER BY queuenumber ASC LIMIT 1";
    $result = $conn->query($sql);
    
    $currentQueue = 0;
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentQueue = $row['queuenumber'];
        
        // Update the database: clear all flags and set the first one
        $conn->query("UPDATE `$table` SET current_serving = 0");
        $conn->query("UPDATE `$table` SET current_serving = 1 WHERE queuenumber = $currentQueue");
        
        echo json_encode([
            'success' => true,
            'current_queue' => $currentQueue,
            'service' => $service,
            'table' => $table,
            'message' => 'Current serving updated to first queue'
        ]);
    } else {
        // No queues in this table
        echo json_encode([
            'success' => true,
            'current_queue' => 0,
            'service' => $service,
            'table' => $table,
            'message' => 'No queues in table'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'current_queue' => 0
    ]);
}

$conn->close();
?>