<?php
require_once 'functions.php';

$conn = db_connect();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT
            as2.status_name AS status,
            COUNT(a.asset_id) AS asset_count
        FROM
            assets a
        JOIN asset_status as2 ON a.status_id = as2.status_id
        GROUP BY
            as2.status_name";

$result = $conn->query($sql);

if ($result === false) {
    error_log("SQL error: " . $conn->error);
    die(json_encode(array('error' => 'Database query failed')));
}

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$statuses = array_column($data, 'status');
$assetCounts = array_column($data, 'asset_count');

$chartData = array(
    'labels' => $statuses,
    'datasets' => array(
        array(
            'label' => 'Asset Count by Status',
            'data' => $assetCounts,
            'backgroundColor' => array(
                'rgba(255, 99, 132, 0.8)',  // Red
                'rgba(54, 162, 235, 0.8)', // Blue
                'rgba(255, 206, 86, 0.8)', // Yellow
                'rgba(75, 192, 192, 0.8)', // Teal
                'rgba(153, 102, 255, 0.8)',// Purple
                'rgba(255, 159, 64, 0.8)'  // Orange
            ),
            'borderWidth' => 1
        )
    )
);

// Return JSON
header('Content-Type: application/json');
echo json_encode($chartData);

$conn->close();
?>