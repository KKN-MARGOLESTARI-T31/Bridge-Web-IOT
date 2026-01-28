<?php
// setup_db_hosting.php - Script Install Tabel via Browser
// Upload ini ke hosting, lalu buka: http://iot-receiver.yzz.me/setup_db_hosting.php

echo "<h1>Setup Database Hosting</h1>";

// 1. Baca Config manual
$host = "sql306.yzz.me"; // Sesuaikan jika beda
// Fallback check .env
if (file_exists('.env')) {
    $env = parse_ini_file('.env');
    if (isset($env['DB_HOST'])) $host = $env['DB_HOST'];
}

// Gunakan credential Anda langsung agar pasti
$user = "yzzme_41011609";
$pass = "xJZhUaQbF4AR";

// Coba connect ke Database Langsung
try {
    // Hardcode nama DB yang sudah kita konfirmasi benar
    $dbname = "yzzme_41011609_iot_database"; 
    
    echo "<p>Menghubungkan ke Database <strong>$dbname</strong>...</p>";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "<p style='color:green'>✅ Koneksi Database Sukses!</p>";
    
    // Create Table Langsung (tanpa USE command lagi karena sudah di DSN)

    
    // Create Table
    $sql = "CREATE TABLE IF NOT EXISTS monitoring_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ph_value DECIMAL(4,2) NOT NULL,
        battery_level DECIMAL(5,2) NOT NULL,
        location VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_loc (location),
        INDEX idx_time (created_at DESC)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Tabel 'monitoring_logs' BERHASIL dibuat/diverifikasi.</p>";
    
    // Insert Dummy Data Test
    $stmt = $pdo->query("SELECT COUNT(*) FROM monitoring_logs");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $pdo->exec("INSERT INTO monitoring_logs (ph_value, battery_level, location) VALUES (7.5, 90.0, 'test_install')");
        echo "<p>✅ Data dummy berhasil dimasukkan.</p>";
    } else {
        echo "<p>Database sudah berisi $count data.</p>";
    }
    
    echo "<h3>Selesai! Sekarang update file .env Anda dengan:</h3>";
    echo "<textarea rows=5 cols=50>
DB_HOST=$host
DB_USER=$user
DB_PASS=$pass
DB_NAME=$dbname
</textarea>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Tips:</strong> Cek Hostname Database di cPanel. Mungkin bukan 'sql208.epizy.com' tapi 'sql208.yzz.me'.</p>";
}
?>
