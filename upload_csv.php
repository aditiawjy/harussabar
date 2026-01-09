<?php
header('Content-Type: application/json');

require_once 'koneksi.php';

try {
    if (!isset($_FILES['csvFile'])) {
        throw new Exception('No file uploaded');
    }
    
    $file = $_FILES['csvFile'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    if ($file['type'] !== 'text/csv' && $file['type'] !== 'application/vnd.ms-excel') {
        throw new Exception('Please upload a CSV file');
    }
    
    // Process CSV
    $csvPath = $file['tmp_name'];
    $handle = fopen($csvPath, 'r');
    
    if (!$handle) {
        throw new Exception('Cannot open uploaded file');
    }
    
    // Skip header
    fgetcsv($handle);
    
    $count = 0;
    $conn->begin_transaction();
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 9) continue;
        if (empty($row[1])) continue;
        
        // Skip if it's the header row
        if ($row[0] === 'id') continue;
        
        // Prepare data
        $match_time = $conn->real_escape_string($row[1]);
        $home_team = $conn->real_escape_string($row[2]);
        $away_team = $conn->real_escape_string($row[3]);
        $league = $conn->real_escape_string($row[4]);
        $fh_home = is_numeric($row[5]) ? (int)$row[5] : 'NULL';
        $fh_away = is_numeric($row[6]) ? (int)$row[6] : 'NULL';
        $ft_home = is_numeric($row[7]) ? (int)$row[7] : 'NULL';
        $ft_away = is_numeric($row[8]) ? (int)$row[8] : 'NULL';
        
        // Insert
        $sql = "INSERT INTO matches (match_time, home_team, away_team, league, fh_home, fh_away, ft_home, ft_away) VALUES " .
               "('$match_time', '$home_team', '$away_team', '$league', $fh_home, $fh_away, $ft_home, $ft_away)";
        
        if (!$conn->query($sql)) {
            throw new Error("Insert failed: " . $conn->error);
        }
        
        $count++;
        
        // Commit every 1000 records
        if ($count % 1000 == 0) {
            $conn->commit();
            $conn->begin_transaction();
        }
    }
    
    $conn->commit();
    fclose($handle);
    
    echo json_encode([
        'success' => true,
        'message' => "✅ Berhasil import $count pertandingan!"
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>
