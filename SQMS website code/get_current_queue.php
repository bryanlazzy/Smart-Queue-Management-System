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
    
    // Get the queue number marked as currently serving
    $sql = "SELECT queuenumber FROM `$table` WHERE current_serving = 1 ORDER BY queuenumber ASC LIMIT 1";
    $result = $conn->query($sql);
    
    $currentQueue = 0;
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentQueue = $row['queuenumber'];
    } else {
        // If no current_serving flag, get the first queue number (fallback)
        $fallbackSql = "SELECT queuenumber FROM `$table` ORDER BY queuenumber ASC LIMIT 1";
        $fallbackResult = $conn->query($fallbackSql);
        
        if ($fallbackResult && $fallbackResult->num_rows > 0) {
            $row = $fallbackResult->fetch_assoc();
            $currentQueue = $row['queuenumber'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'current_queue' => $currentQueue,
        'service' => $service,
        'table' => $table
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'current_queue' => 0
    ]);
}

$conn->close();
?>