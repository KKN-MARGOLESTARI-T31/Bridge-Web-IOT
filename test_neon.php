&lt;?php
// test_neon.php - Quick Neon Connection Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Neon Connection Test&lt;/title&gt;
    &lt;style&gt;
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #00D4AA;
            padding-bottom: 10px;
        }
        .success {
            color: #00D4AA;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-family: monospace;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #00D4AA;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #00D4AA;
            color: white;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #00D4AA;
            color: white;
        }
        .badge-error {
            background: #e74c3c;
            color: white;
        }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div class='container'&gt;
        &lt;h1&gt;🧪 Neon Database Connection Test&lt;/h1&gt;";

// Load .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "&lt;div class='section'&gt;
            &lt;span class='error'&gt;❌ File .env tidak ditemukan!&lt;/span&gt;&lt;br&gt;
            &lt;p&gt;Silakan:&lt;/p&gt;
            &lt;ol&gt;
                &lt;li&gt;Copy file .env.example menjadi .env&lt;/li&gt;
                &lt;li&gt;Isi DATABASE_URL dengan connection string dari Neon&lt;/li&gt;
                &lt;li&gt;Refresh halaman ini&lt;/li&gt;
            &lt;/ol&gt;
            &lt;p&gt;Lihat &lt;code&gt;SETUP_NEON.md&lt;/code&gt; untuk panduan lengkap.&lt;/p&gt;
          &lt;/div&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;";
    exit;
}

// Parse .env
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$db_url = $_ENV['DATABASE_URL'] ?? '';

if (empty($db_url)) {
    echo "&lt;div class='section'&gt;
            &lt;span class='error'&gt;❌ DATABASE_URL kosong!&lt;/span&gt;&lt;br&gt;
            &lt;p&gt;Silakan isi DATABASE_URL di file .env dengan connection string dari Neon.&lt;/p&gt;
            &lt;p&gt;Lihat &lt;code&gt;SETUP_NEON.md&lt;/code&gt; untuk panduan lengkap.&lt;/p&gt;
          &lt;/div&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;";
    exit;
}

// Parse URL
$parsed = parse_url($db_url);
$db_host = $parsed['host'] ?? '';
$db_port = $parsed['port'] ?? 5432;
$db_user = $parsed['user'] ?? '';
$db_pass = isset($parsed['pass']) ? '***' : 'not set';
$db_name = ltrim($parsed['path'] ?? '', '/');

echo "&lt;div class='section'&gt;
        &lt;h3&gt;📋 Configuration&lt;/h3&gt;
        &lt;table&gt;
            &lt;tr&gt;&lt;th&gt;Parameter&lt;/th&gt;&lt;th&gt;Value&lt;/th&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;Host&lt;/td&gt;&lt;td&gt;&lt;code&gt;$db_host&lt;/code&gt;&lt;/td&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;Port&lt;/td&gt;&lt;td&gt;&lt;code&gt;$db_port&lt;/code&gt;&lt;/td&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;Database&lt;/td&gt;&lt;td&gt;&lt;code&gt;$db_name&lt;/code&gt;&lt;/td&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;User&lt;/td&gt;&lt;td&gt;&lt;code&gt;$db_user&lt;/code&gt;&lt;/td&gt;&lt;/tr&gt;
            &lt;tr&gt;&lt;td&gt;Password&lt;/td&gt;&lt;td&gt;&lt;code&gt;$db_pass&lt;/code&gt;&lt;/td&gt;&lt;/tr&gt;
        &lt;/table&gt;
      &lt;/div&gt;";

// Check PHP PostgreSQL extension
if (!extension_loaded('pgsql') && !extension_loaded('pdo_pgsql')) {
    echo "&lt;div class='section'&gt;
            &lt;span class='error'&gt;❌ PostgreSQL extension tidak terinstall!&lt;/span&gt;&lt;br&gt;&lt;br&gt;
            &lt;p&gt;Install dengan:&lt;/p&gt;
            &lt;div class='info'&gt;
                &lt;strong&gt;Ubuntu/Debian:&lt;/strong&gt;&lt;br&gt;
                sudo apt-get install php-pgsql&lt;br&gt;
                sudo systemctl restart apache2&lt;br&gt;&lt;br&gt;
                &lt;strong&gt;Windows (XAMPP):&lt;/strong&gt;&lt;br&gt;
                Edit php.ini, uncomment: extension=pgsql&lt;br&gt;
                Restart Apache
            &lt;/div&gt;
          &lt;/div&gt;
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;";
    exit;
}

echo "&lt;div class='section'&gt;
        &lt;span class='success'&gt;✓ PostgreSQL extension loaded&lt;/span&gt;
      &lt;/div&gt;";

// Try connection
echo "&lt;div class='section'&gt;
        &lt;h3&gt;🔌 Connection Test&lt;/h3&gt;";

$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name;sslmode=require";

try {
    $pdo = new PDO($dsn, $db_user, isset($parsed['pass']) ? $parsed['pass'] : '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "&lt;span class='success'&gt;✅ Connection successful!&lt;/span&gt;&lt;br&gt;&lt;br&gt;";
    
    // Get PostgreSQL version
    $version = $pdo->query("SELECT version()")->fetchColumn();
    echo "&lt;p&gt;&lt;strong&gt;PostgreSQL Version:&lt;/strong&gt;&lt;br&gt;&lt;code&gt;$version&lt;/code&gt;&lt;/p&gt;";
    
    // Check tables
    echo "&lt;h3&gt;📊 Database Tables&lt;/h3&gt;";
    $tables = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'BASE TABLE'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "&lt;p&gt;&lt;span class='badge badge-error'&gt;No tables found&lt;/span&gt;&lt;/p&gt;";
        echo "&lt;p&gt;Jalankan setup database:&lt;/p&gt;";
        echo "&lt;div class='info'&gt;php setup_db_neon.php&lt;/div&gt;";
    } else {
        echo "&lt;table&gt;";
        echo "&lt;tr&gt;&lt;th&gt;Table Name&lt;/th&gt;&lt;th&gt;Row Count&lt;/th&gt;&lt;/tr&gt;";
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "&lt;tr&gt;&lt;td&gt;$table&lt;/td&gt;&lt;td&gt;$count&lt;/td&gt;&lt;/tr&gt;";
        }
        echo "&lt;/table&gt;";
    }
    
    // Test query
    if (in_array('ph_readings', $tables)) {
        echo "&lt;h3&gt;🧪 Sample Data (Latest 5 pH Readings)&lt;/h3&gt;";
        $readings = $pdo->query("SELECT * FROM ph_readings ORDER BY timestamp DESC LIMIT 5")->fetchAll();
        
        if (empty($readings)) {
            echo "&lt;p&gt;No data yet. Seed data dengan: &lt;code&gt;php seed.php&lt;/code&gt;&lt;/p&gt;";
        } else {
            echo "&lt;table&gt;";
            echo "&lt;tr&gt;&lt;th&gt;ID&lt;/th&gt;&lt;th&gt;pH Value&lt;/th&gt;&lt;th&gt;Timestamp&lt;/th&gt;&lt;th&gt;Device ID&lt;/th&gt;&lt;/tr&gt;";
            foreach ($readings as $row) {
                echo "&lt;tr&gt;";
                echo "&lt;td&gt;{$row['id']}&lt;/td&gt;";
                echo "&lt;td&gt;{$row['ph_value']}&lt;/td&gt;";
                echo "&lt;td&gt;{$row['timestamp']}&lt;/td&gt;";
                echo "&lt;td&gt;" . ($row['device_id'] ?? 'N/A') . "&lt;/td&gt;";
                echo "&lt;/tr&gt;";
            }
            echo "&lt;/table&gt;";
        }
    }
    
} catch (PDOException $e) {
    echo "&lt;span class='error'&gt;❌ Connection failed!&lt;/span&gt;&lt;br&gt;&lt;br&gt;";
    echo "&lt;p&gt;&lt;strong&gt;Error:&lt;/strong&gt;&lt;/p&gt;";
    echo "&lt;div class='info'&gt;" . htmlspecialchars($e->getMessage()) . "&lt;/div&gt;";
    echo "&lt;p&gt;&lt;strong&gt;Troubleshooting:&lt;/strong&gt;&lt;/p&gt;";
    echo "&lt;ol&gt;";
    echo "&lt;li&gt;Periksa kembali DATABASE_URL di file .env&lt;/li&gt;";
    echo "&lt;li&gt;Pastikan connection string dari Neon Dashboard benar&lt;/li&gt;";
    echo "&lt;li&gt;Pastikan ada ?sslmode=require di akhir URL&lt;/li&gt;";
    echo "&lt;li&gt;Cek apakah IP Anda di-whitelist (jika diaktifkan di Neon)&lt;/li&gt;";
    echo "&lt;/ol&gt;";
}

echo "&lt;/div&gt;";

echo "&lt;div class='section'&gt;
        &lt;h3&gt;📚 Next Steps&lt;/h3&gt;
        &lt;ul&gt;
            &lt;li&gt;Setup tables: &lt;code&gt;php setup_db_neon.php&lt;/code&gt;&lt;/li&gt;
            &lt;li&gt;Seed data: &lt;code&gt;php seed.php&lt;/code&gt;&lt;/li&gt;
            &lt;li&gt;Test API: &lt;code&gt;curl http://localhost/api/save-ph.php&lt;/code&gt;&lt;/li&gt;
            &lt;li&gt;Read guide: &lt;code&gt;SETUP_NEON.md&lt;/code&gt;&lt;/li&gt;
        &lt;/ul&gt;
      &lt;/div&gt;";

echo "    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;";
?>
