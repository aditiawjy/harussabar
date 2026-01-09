<?php
/**
 * Konfigurasi Koneksi Database
 */

// Menggunakan getenv untuk mendukung environment variables, dengan fallback ke nilai default yang diberikan
$db_host = getenv('DB_HOST') ?: "hipbiz.id";
$db_user = getenv('DB_USER') ?: "u2823579_test";
$db_pass = getenv('DB_PASSWORD') ?: "u2823579_test";
$db_name = getenv('DB_NAME') ?: "u2823579_test2";
$db_port = (int)(getenv('DB_PORT') ?: 3306);

// Membuat koneksi menggunakan mysqli dengan opsi LOCAL INFILE
$conn = mysqli_init();
$conn->options(MYSQLI_OPT_LOCAL_INFILE, true);
if (!$conn->real_connect($db_host, $db_user, $db_pass, $db_name, $db_port)) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset ke utf8mb4 untuk mendukung karakter khusus
$conn->set_charset("utf8mb4");

// Fungsi untuk membuat tabel jika belum ada (opsional, untuk memastikan struktur database siap)
function checkAndCreateMatchesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        match_time DATETIME,
        home_team VARCHAR(255),
        away_team VARCHAR(255),
        league VARCHAR(255) DEFAULT NULL,
        fh_home INT,
        fh_away INT,
        ft_home INT,
        ft_away INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    return $conn->query($sql);
}
?>
