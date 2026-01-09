<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

// Get unique leagues from database
$result = $conn->query("SELECT DISTINCT league FROM matches WHERE league IS NOT NULL AND league != '' ORDER BY league");

$leagues = [];
while ($row = $result->fetch_assoc()) {
    $leagues[] = $row['league'];
}

echo json_encode([
    'success' => true,
    'leagues' => $leagues
]);

$conn->close();
?>
