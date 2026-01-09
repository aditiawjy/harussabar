<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'koneksi.php';

try {
    // Get POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['matches']) || !is_array($data['matches'])) {
        throw new Exception('Invalid data format');
    }
    
    $matches = $data['matches'];
    $successCount = 0;
    $errors = [];
    
    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO matches (match_time, home_team, away_team, league, fh_home, fh_away, ft_home, ft_away) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    foreach ($matches as $index => $match) {
        // Validate required fields
        if (!isset($match['match_time']) || !isset($match['home_team']) || !isset($match['away_team'])) {
            $errors[] = "Match " . ($index + 1) . ": Missing required fields";
            continue;
        }
        
        // Convert datetime format if needed
        $datetime = $match['match_time'];
        
        // Parse and reformat datetime to MySQL format
        $dateObj = DateTime::createFromFormat('Y-m-d h:i A', $datetime);
        if ($dateObj) {
            $mysqlDatetime = $dateObj->format('Y-m-d H:i:s');
        } else {
            // Try other formats
            $dateObj = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
            if ($dateObj) {
                $mysqlDatetime = $datetime;
            } else {
                $errors[] = "Match " . ($index + 1) . ": Invalid datetime format";
                continue;
            }
        }
        
        // Bind parameters
        $stmt->bind_param('ssssiiii', 
            $mysqlDatetime,
            $match['home_team'],
            $match['away_team'],
            $match['league'] ?? 'SABA CLUB FRIENDLY',
            $match['fh_home'] ?? 0,
            $match['fh_away'] ?? 0,
            $match['ft_home'] ?? 0,
            $match['ft_away'] ?? 0
        );
        
        // Execute
        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errors[] = "Match " . ($index + 1) . ": " . $stmt->error;
        }
    }
    
    // Commit or rollback
    if ($successCount > 0) {
        $conn->commit();
    } else {
        $conn->rollback();
    }
    
    $stmt->close();
    $conn->close();
    
    // Return response
    echo json_encode([
        'success' => true,
        'count' => $successCount,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
