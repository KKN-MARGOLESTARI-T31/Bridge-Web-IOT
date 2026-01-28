<?php
// debug_hosting.php - Upload file ini ke hosting untuk cek status lengkap
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Hosting Status V2</h1>";

// 1. Cek File .env
$envPath = __DIR__ . '/.env';
echo "<h2>1. Cek File .env</h2>";
if (file_exists($envPath)) {
    echo "<p style='color:green'>✅ File .env ditemukan.</p>";
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k); $v = trim($v);
            if ($k == 'DB_PASS') $v = substr($v, 0, 3) . '******';
            echo "$k = $v<br>";
        }
    }
} else {
    echo "<p style='color:red'>❌ File .env TIDAK DITEMUKAN!</p>";
}

// 2. Test Koneksi Manual Sesuai .env
echo "<h2>3. Test Koneksi Manual Sesuai .env</h2>";
// Load env manual
$dbHost = ''; $dbUser = ''; $dbPass = ''; $dbName = '';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key); $value = trim($value);
            if ($key == 'DB_HOST') $dbHost = $value;
            if ($key == 'DB_USER') $dbUser = $value;
            if ($key == 'DB_PASS') $dbPass = $value;
            if ($key == 'DB_NAME') $dbName = $value;
        }
    }
}

echo "Mencoba connect ke:<br>";
echo "Host: <strong>$dbHost</strong><br>";
echo "User: <strong>$dbUser</strong><br>";
echo "DB: <strong>$dbName</strong><br>";

// Test mysqli
$conn = mysqli_init();
if (@$conn->real_connect($dbHost, $dbUser, $dbPass, $dbName)) {
    echo "<h3 style='color:green'>✅ KONEKSI SUKSES via MySQLi!</h3>";
    echo "Server Info: " . $conn->host_info . "<br>";
    $conn->close();
} else {
    echo "<h3 style='color:red'>❌ KONEKSI GAGAL via MySQLi</h3>";
    echo "Error: " . $conn->connect_error . "<br>";
    if (strpos($conn->connect_error, 'Access denied') !== false) {
        echo "Artinya: User/Pass benar, tapi user '$dbUser' DITOLAK mengakses database '$dbName'.<br>";
        echo "Solusi: Cek nama database Anda di cPanel. Apakah benar '$dbName'? Atau mungkin '$dbUser" . "_iot'?";
    }
}
?>
