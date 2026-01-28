<?php
// test_db_direct.php - V2 (Multi-Host Check)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user = 'yzzme_41011609';
$pass = 'xJZhUaQbF4AR'; 
$dbname = 'yzzme_41011609_iot_database';

// Daftar kemungkinan host yang sering dipakai hosting group ini
$hosts_to_try = [
    'sql306.yzz.me',
    'sql306.epizy.com', 
    '127.0.0.1',
    'localhost'
];

echo "<h1>Test Koneksi Multi-Host</h1>";
echo "User: $user<br>";
echo "DB: $dbname<br><hr>";

foreach ($hosts_to_try as $host) {
    echo "<h3>Mencoba Host: <code>$host</code></h3>";
    try {
        // Set timeout kecil biar ga nunggu lama
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        
        // Suppress warning biar rapi, kita tangkap error-nya
        @$conn->real_connect($host, $user, $pass);
        
        if ($conn->connect_error) {
            $err = $conn->connect_error;
            echo "<span style='color:red'>GAGAL: $err</span>";
            
            // Analisa Error
            if (strpos($err, 'Access denied') !== false) {
                echo "<br>👉 Server merespon, tapi password/user ditolak.";
            } elseif (strpos($err, 'Unknown MySQL server') !== false || strpos($err, 'Connection timed out') !== false) {
                echo "<br>👉 Server tidak ditemukan/down.";
            }
        } else {
            echo "<span style='color:green; font-weight:bold; font-size:1.2em'>BERHASIL LOGIN! ✅</span>";
            echo "<br>Host yang benar adalah: <strong>$host</strong>";
            $conn->close();
            
            // Kalau berhasil, kita stop loop
            echo "<br><br><strong>SILAKAN UPDATE .ENV ANDA DENGAN HOST DI ATAS!</strong>";
            exit;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    echo "<hr>";
}

echo "<h3>Kesimpulan:</h3>";
echo "Jika semua host di atas GAGAL dengan error 'Access denied', maka masalahnya 100% ada di <strong>PASSWORD DATABASE</strong> yang tidak sinkron.";
echo "<br>Solusi satu-satunya adalah Reset Password via cPanel.";
?>
