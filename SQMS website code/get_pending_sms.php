<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'config.php';

try {
    $pending = array();
    
    // Proximity thresholds
    define('NEAR_THRESHOLD', 5);
    define('NEXT_THRESHOLD', 1);
    
    // OPTIMIZATION: Add pagination support for large datasets
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50; // Max 50 per request
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    $tables = [
        'assessment_window' => 'Assessment Window',
        'posting_unholding_account' => 'Posting and Unholding of Account',
        'cashier_service' => 'Cashier Window',
        'other_service' => 'Others'
    ];
    
    foreach ($tables as $table => $serviceName) {
        // Get current serving queue number for this table
        $currentServingSql = "SELECT queuenumber FROM `$table` WHERE current_serving = 1 LIMIT 1";
        $currentResult = $conn->query($currentServingSql);
        
        $currentQueue = 0;
        if ($currentResult && $currentResult->num_rows > 0) {
            $currentRow = $currentResult->fetch_assoc();
            $currentQueue = (int)$currentRow['queuenumber'];
        } else {
            // Fallback: get first queue number if no current_serving flag
            $fallbackSql = "SELECT queuenumber FROM `$table` ORDER BY queuenumber ASC LIMIT 1";
            $fallbackResult = $conn->query($fallbackSql);
            if ($fallbackResult && $fallbackResult->num_rows > 0) {
                $row = $fallbackResult->fetch_assoc();
                $currentQueue = (int)$row['queuenumber'];
            }
        }
        
        // OPTIMIZATION: Only fetch entries that need notifications
        // This reduces data transfer and processing
        $sql = "SELECT 
                    queuenumber, 
                    name, 
                    contactnumber,
                    sms_registered_sent,
                    sms_near_sent,
                    sms_next_sent,
                    current_serving
                FROM `$table` 
                WHERE (
                    sms_registered_sent = 0 
                    OR (queuenumber <= ? AND sms_near_sent = 0)
                    OR (queuenumber <= ? AND sms_next_sent = 0)
                )
                ORDER BY queuenumber ASC
                LIMIT ? OFFSET ?";
        
        $nearLimit = $currentQueue + NEAR_THRESHOLD;
        $nextLimit = $currentQueue + NEXT_THRESHOLD;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $nearLimit, $nextLimit, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $queueNum = (int)$row['queuenumber'];
                $distance = $queueNum - $currentQueue;
                
                $notificationType = null;
                
                // Determine which notification to send (PRIORITY ORDER)
                if ($row['sms_registered_sent'] == 0) {
                    $notificationType = 'registered';
                } 
                elseif ($distance <= NEXT_THRESHOLD && $distance > 0 && $row['sms_next_sent'] == 0) {
                    $notificationType = 'next';
                } 
                elseif ($distance <= NEAR_THRESHOLD && $distance > NEXT_THRESHOLD && $row['sms_near_sent'] == 0) {
                    $notificationType = 'near';
                }
                
                // Only add if there's a notification to send
                if ($notificationType !== null) {
                    $row['notification_type'] = $notificationType;
                    $row['service'] = $serviceName;
                    $row['table'] = $table;
                    $row['current_queue'] = $currentQueue;
                    $row['distance'] = $distance;
                    $pending[] = $row;
                }
            }
        }
        $stmt->close();
    }
    
    // OPTIMIZATION: Sort by priority
    // 1. Registered (highest priority)
    // 2. Next
    // 3. Near
    usort($pending, function($a, $b) {
        $priority = ['registered' => 1, 'next' => 2, 'near' => 3];
        $aPriority = $priority[$a['notification_type']] ?? 99;
        $bPriority = $priority[$b['notification_type']] ?? 99;
        
        if ($aPriority === $bPriority) {
            return $a['queuenumber'] - $b['queuenumber'];
        }
        return $aPriority - $bPriority;
    });
    
    echo json_encode([
        "success" => true,
        "pending" => $pending,
        "count" => count($pending),
        "limit" => $limit,
        "offset" => $offset,
        "timestamp" => date('Y-m-d H:i:s'),
        "memory_usage" => memory_get_usage(true)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
        "pending" => []
    ]);
}

$conn->close();
?>