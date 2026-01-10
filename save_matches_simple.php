<?php
// Update jika waktu dan tim sama, insert jika baru
header('Content-Type: application/json');

require_once 'koneksi.php';

// Get data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$inserted = 0;
$updated = 0;

if (isset($data['matches'])) {
    foreach ($data['matches'] as $match) {
        // Convert datetime
        $date = DateTime::createFromFormat('Y-m-d h:i A', $match['match_time']);
        $datetime = $date ? $date->format('Y-m-d H:i:s') : $match['match_time'];
        
        // Escape values
        $home_team = $conn->real_escape_string($match['home_team']);
        $away_team = $conn->real_escape_string($match['away_team']);
        $league = $conn->real_escape_string($match['league']);
        
        // Check if match exists with same teams and date (ignoring time)
        $check_sql = "SELECT id, match_time, ft_home, ft_away, fh_home, fh_away 
                      FROM matches 
                      WHERE ((home_team = '$home_team' AND away_team = '$away_team') 
                           OR (home_team = '$away_team' AND away_team = '$home_team'))
                      AND DATE(match_time) = DATE('$datetime')";
        
        $result = $conn->query($check_sql);
        
        if ($result && $result->num_rows > 0) {
            // Match exists - check if time is different
            $existing = $result->fetch_assoc();
            $existing_time = new DateTime($existing['match_time']);
            $new_time = new DateTime($datetime);
            
            // Compare only date, not time
            $same_day = $existing_time->format('Y-m-d') == $new_time->format('Y-m-d');
            $different_time = $existing_time->format('H:i') != $new_time->format('H:i');
            
            if ($same_day && $different_time) {
                // Same day but different time - insert as new match
                $fh_home = $match['fh_home'] === null ? 'NULL' : $match['fh_home'];
                $fh_away = $match['fh_away'] === null ? 'NULL' : $match['fh_away'];
                $ft_home = $match['ft_home'] === null ? 'NULL' : $match['ft_home'];
                $ft_away = $match['ft_away'] === null ? 'NULL' : $match['ft_away'];
                
                $sql = "INSERT INTO matches (match_time, home_team, away_team, league, fh_home, fh_away, ft_home, ft_away) VALUES " .
                       "('$datetime', '$home_team', '$away_team', '$league', " .
                       "$fh_home, $fh_away, $ft_home, $ft_away)";
                
                if ($conn->query($sql)) {
                    $inserted++;
                }
            } else {
                // Same match - update scores if they're different
                $fh_home = $match['fh_home'] === null ? 'NULL' : $match['fh_home'];
                $fh_away = $match['fh_away'] === null ? 'NULL' : $match['fh_away'];
                $ft_home = $match['ft_home'] === null ? 'NULL' : $match['ft_home'];
                $ft_away = $match['ft_away'] === null ? 'NULL' : $match['ft_away'];
                
                // Only update if scores are different
                $scores_different = (
                    $existing['fh_home'] != $match['fh_home'] ||
                    $existing['fh_away'] != $match['fh_away'] ||
                    $existing['ft_home'] != $match['ft_home'] ||
                    $existing['ft_away'] != $match['ft_away']
                );
                
                if ($scores_different) {
                    $sql = "UPDATE matches SET " .
                           "match_time = '$datetime', " .
                           "fh_home = $fh_home, " .
                           "fh_away = $fh_away, " .
                           "ft_home = $ft_home, " .
                           "ft_away = $ft_away, " .
                           "league = '$league' " .
                           "WHERE id = " . $existing['id'];
                    
                    if ($conn->query($sql)) {
                        $updated++;
                    }
                }
            }
        } else {
            // New match - insert
            $fh_home = $match['fh_home'] === null ? 'NULL' : $match['fh_home'];
            $fh_away = $match['fh_away'] === null ? 'NULL' : $match['fh_away'];
            $ft_home = $match['ft_home'] === null ? 'NULL' : $match['ft_home'];
            $ft_away = $match['ft_away'] === null ? 'NULL' : $match['ft_away'];
            
            $sql = "INSERT INTO matches (match_time, home_team, away_team, league, fh_home, fh_away, ft_home, ft_away) VALUES " .
                   "('$datetime', '$home_team', '$away_team', '$league', " .
                   "$fh_home, $fh_away, $ft_home, $ft_away)";
            
            if ($conn->query($sql)) {
                $inserted++;
            }
        }
    }
}

$message = [];
if ($inserted > 0) {
    $message[] = "$inserted pertandingan baru ditambahkan";
}
if ($updated > 0) {
    $message[] = "$updated pertandingan diperbarui";
}
if (empty($message)) {
    $message[] = "Tidak ada perubahan data";
}

echo json_encode([
    'success' => true,
    'inserted' => $inserted,
    'updated' => $updated,
    'message' => "✅ BERHASIL! " . implode(', ', $message),
    'refreshLeagues' => true
]);
?>
