<?php
// Simple version - no error handling, just save
header('Content-Type: application/json');

require_once 'koneksi.php';

// Get data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$count = 0;

if (isset($data['matches'])) {
    foreach ($data['matches'] as $match) {
        // Convert datetime
        $date = DateTime::createFromFormat('Y-m-d h:i A', $match['match_time']);
        $datetime = $date ? $date->format('Y-m-d H:i:s') : $match['match_time'];
        
        // Insert dengan proper NULL handling
        $fh_home = $match['fh_home'] === null ? 'NULL' : $match['fh_home'];
        $fh_away = $match['fh_away'] === null ? 'NULL' : $match['fh_away'];
        $ft_home = $match['ft_home'] === null ? 'NULL' : $match['ft_home'];
        $ft_away = $match['ft_away'] === null ? 'NULL' : $match['ft_away'];
        $league = $conn->real_escape_string($match['league']);
        
        $sql = "INSERT INTO matches (match_time, home_team, away_team, league, fh_home, fh_away, ft_home, ft_away) VALUES " .
               "('$datetime', '{$match['home_team']}', '{$match['away_team']}', '$league', " .
               "$fh_home, $fh_away, $ft_home, $ft_away)";
        
        if ($conn->query($sql)) {
            $count++;
        }
    }
}

echo json_encode([
    'success' => true,
    'count' => $count,
    'message' => "✅ BERHASIL! $count pertandingan tersimpan",
    'refreshLeagues' => true // Signal to refresh leagues dropdown
]);
?>
