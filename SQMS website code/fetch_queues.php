<?php
include 'config.php';

$data = [
    'window1' => [],
    'window2' => [],
    'window3' => [],
    'window4' => []
];

// Fetch from each table
$res1 = $conn->query("SELECT queuenumber FROM assessment_window ORDER BY queuenumber ASC");
while ($row = $res1->fetch_assoc()) {
    $data['window1'][] = $row['queuenumber'];
}

$res2 = $conn->query("SELECT queuenumber FROM posting_unholding_account ORDER BY queuenumber ASC");
while ($row = $res2->fetch_assoc()) {
    $data['window2'][] = $row['queuenumber'];
}

$res3 = $conn->query("SELECT queuenumber FROM cashier_service ORDER BY queuenumber ASC");
while ($row = $res3->fetch_assoc()) {
    $data['window3'][] = $row['queuenumber'];
}

$res4 = $conn->query("SELECT queuenumber FROM other_service ORDER BY queuenumber ASC");
while ($row = $res4->fetch_assoc()) {
    $data['window4'][] = $row['queuenumber'];
}

header('Content-Type: application/json');
echo json_encode($data);
?>