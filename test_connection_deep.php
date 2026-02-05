<?php
// test_connection_deep.php
// Script untuk tes koneksi Database REAL ke Neon via PSQL CLI
// Upload ini ke server Anda

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Deep Database Connection Test</h2>";

// 1. Cek File .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ CRITICAL: File .env TIDAK DITEMUKAN di " . __DIR__);
}
echo "✅ File .env ditemukan.<br>";

// 2. Baca isi .env (Hanya ambil DATABASE_URL)
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$dbUrl = '';
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, 'DATABASE_URL=') === 0) {
        $parts = explode('=', $line, 2);
        $dbUrl = trim($parts[1] ?? '');
        break;
    }
}

if (empty($dbUrl)) {
    die("❌ CRITICAL: DATABASE_URL tidak ditemukan di dalam .env");
}

// Masking URL untuk keamanan tampilan
$maskedUrl = preg_replace('/(:)([^@]+)(@)/', '$1****$3', $dbUrl);
echo "ℹ️ Connection String loaded: <code>$maskedUrl</code><br>";

// 3. Tes Eksekusi PSQL
echo "<hr><h3>🚀 Testing Connection...</h3>";

$sql = "SELECT 'CONNECTION_OK' as status, NOW() as server_time, current_user, current_database();";
$tmpFile = tempnam(sys_get_temp_dir(), 'test_sql_');
file_put_contents($tmpFile, $sql);

// Command: Gunakan flag -w (no password prompt) tapi psql harusnya pakai URI
// Kita capture STDERR juga (2>&1)
$cmd = sprintf('psql "%s" -t -A -f "%s" 2>&1', $dbUrl, $tmpFile);

echo "Executing command...<br>";
$start = microtime(true);
$output = shell_exec($cmd);
$duration = microtime(true) - $start;

unlink($tmpFile);

echo "Time: " . number_format($duration, 3) . "s<br>";
echo "<h3>📝 Raw Output:</h3>";
echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>";
echo htmlspecialchars($output);
echo "</pre>";

// 4. Analisis Hasil
if (strpos($output, 'CONNECTION_OK') !== false) {
    echo "<h3 style='color:green;'>✅ SUCCESS! Bridge Terhubung ke Database.</h3>";
    echo "Masalah 'Bridge tidak kehubung' mungkin sudah fix, atau ada masalah di query spesifik (bukan koneksi).";
} else {
    echo "<h3 style='color:red;'>❌ CONNECTION FAILED!</h3>";
    echo "<strong>Kemungkinan Penyebab:</strong><ul>";
    if (strpos($output, 'FATAL: password authentication failed') !== false) {
        echo "<li>Password di .env salah! Cek username/password NeonDB Anda.</li>";
    }
    if (strpos($output, 'could not connect to server') !== false) {
        echo "<li>Firewall Server memblokir Port 5432 (Outbound).</li>";
        echo "<li>Database Host salah/typo.</li>";
    }
    if (strpos($output, 'not found') !== false) {
        echo "<li>Program 'psql' belum terinstall (sudo apt install postgresql-client).</li>";
    }
    echo "</ul>";
}
?>
