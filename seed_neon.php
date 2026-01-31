&lt;?php
// seed_neon.php - Complete Seeding untuk Neon PostgreSQL (3 Tables)
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Complete Seeding untuk Neon PostgreSQL ===\n\n";

// Load config
require_once 'config.php';

echo "✓ Connected to database\n\n";

// ==============================================
// 1. CREATE DEVICE_STATUS TABLE (if not exists)
// ==============================================
echo "Step 1: Creating device_status table (if not exists)...\n";

$sql_create_device_status = "
CREATE TABLE IF NOT EXISTS device_status (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(50) NOT NULL,
    mode VARCHAR(20) DEFAULT 'active',
    battery_level DECIMAL(5,2),
    signal_strength INTEGER,
    firmware_version VARCHAR(20),
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_device_status_device_id ON device_status(device_id);
CREATE INDEX IF NOT EXISTS idx_device_status_last_seen ON device_status(last_seen DESC);
";

try {
    $pdo->exec($sql_create_device_status);
    echo "  ✓ Table device_status ready\n\n";
} catch (PDOException $e) {
    echo "  ⚠ Table might already exist: " . $e->getMessage() . "\n\n";
}

// ==============================================
// 2. INSERT DEVICE STATUS (5 devices)
// ==============================================
echo "Step 2: Inserting device status data...\n";

$devices_data = [
    ['ESP32_001', 'active', 87.5, -45, 'v1.2.3', 5],
    ['ESP32_002', 'active', 92.0, -52, 'v1.2.3', 2],
    ['ESP32_003', 'sleep', 45.3, -68, 'v1.2.2', 60],
    ['IoT_Device_A', 'active', 78.8, -38, 'v1.3.0', 10],
    ['IoT_Device_B', 'maintenance', 100.0, -42, 'v1.3.0', 30],
];

$sql_device = "INSERT INTO device_status (device_id, mode, battery_level, signal_strength, firmware_version, last_seen) 
               VALUES (:device_id, :mode, :battery, :signal, :firmware, :last_seen)";
$stmt_device = $pdo->prepare($sql_device);

$inserted_devices = 0;
foreach ($devices_data as $device) {
    $last_seen = date('Y-m-d H:i:s', time() - ($device[5] * 60));
    
    try {
        $stmt_device->execute([
            ':device_id' => $device[0],
            ':mode' => $device[1],
            ':battery' => $device[2],
            ':signal' => $device[3],
            ':firmware' => $device[4],
            ':last_seen' => $last_seen
        ]);
        $inserted_devices++;
        echo "  [{$inserted_devices}/5] {$device[0]}: {$device[1]}, Battery: {$device[2]}%, Signal: {$device[3]} dBm\n";
    } catch (PDOException $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ==============================================
// 3. INSERT PH READINGS (20 data)
// ==============================================
echo "Step 3: Inserting pH readings...\n";

$device_ids = ['ESP32_001', 'ESP32_002', 'ESP32_003', 'IoT_Device_A', 'IoT_Device_B'];
$ph_data = [
    // Hari ini (1-5 jam lalu)
    [7.2, 'ESP32_001', 'pH normal - sawah bagian utara', 1],
    [6.8, 'ESP32_002', 'pH sedikit asam - kolam ikan', 2],
    [7.5, 'ESP32_003', 'pH optimal - sumur', 3],
    [6.5, 'IoT_Device_A', 'pH rendah - sungai', 4],
    [7.0, 'IoT_Device_B', 'pH normal - tandon air', 5],
    // Kemarin
    [7.3, 'ESP32_001', 'pH stabil - sawah', 26],
    [6.9, 'ESP32_002', 'pH normal - kolam', 28],
    [7.1, 'ESP32_003', 'pH baik - sumur', 30],
    [6.7, 'IoT_Device_A', 'pH menurun - sungai', 32],
    [7.4, 'IoT_Device_B', 'pH tinggi - tandon', 34],
    // 2 hari lalu
    [7.0, 'ESP32_001', 'pH normal - sawah', 51],
    [6.6, 'ESP32_002', 'pH rendah - kolam', 53],
    [7.6, 'ESP32_003', 'pH tinggi - sumur', 55],
    [6.4, 'IoT_Device_A', 'pH sangat rendah - sungai', 57],
    [7.2, 'IoT_Device_B', 'pH normal - tandon', 59],
    // 3-7 hari lalu
    [6.9, 'ESP32_001', 'Data historis', 72],
    [7.1, 'ESP32_002', 'Data historis', 96],
    [7.3, 'ESP32_003', 'Data historis', 120],
    [6.8, 'IoT_Device_A', 'Data historis', 144],
    [7.5, 'IoT_Device_B', 'Data historis', 168],
];

$sql_ph = "INSERT INTO ph_readings (ph_value, device_id, notes, timestamp) VALUES (:ph, :device, :notes, :timestamp)";
$stmt_ph = $pdo->prepare($sql_ph);

$inserted_ph = 0;
foreach ($ph_data as $data) {
    $timestamp_str = date('Y-m-d H:i:s', time() - ($data[3] * 3600));
    
    try {
        $stmt_ph->execute([
            ':ph' => $data[0],
            ':device' => $data[1],
            ':notes' => $data[2],
            ':timestamp' => $timestamp_str
        ]);
        $inserted_ph++;
        echo "  [{$inserted_ph}/20] pH: {$data[0]} | Device: {$data[1]}\n";
    } catch (PDOException $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ==============================================
// 4. INSERT WATER LEVEL READINGS (20 data)
// ==============================================
echo "Step 4: Inserting water level readings...\n";

$water_data = [
    // Hari ini
    [45, 'ESP32_001', 'Level normal - sawah', 1],
    [78, 'ESP32_002', 'Level tinggi - kolam', 2],
    [32, 'ESP32_003', 'Level rendah - sumur', 3],
    [91, 'IoT_Device_A', 'Level maksimal - sungai', 4],
    [56, 'IoT_Device_B', 'Level sedang - tandon', 5],
    // Kemarin
    [48, 'ESP32_001', 'Level naik - sawah', 26],
    [82, 'ESP32_002', 'Level tinggi - kolam', 28],
    [28, 'ESP32_003', 'Level turun - sumur', 30],
    [95, 'IoT_Device_A', 'Level overflow - sungai', 32],
    [60, 'IoT_Device_B', 'Level naik - tandon', 34],
    // 2 hari lalu
    [42, 'ESP32_001', 'Level turun - sawah', 51],
    [75, 'ESP32_002', 'Level normal - kolam', 53],
    [35, 'ESP32_003', 'Level naik sedikit - sumur', 55],
    [88, 'IoT_Device_A', 'Level tinggi - sungai', 57],
    [52, 'IoT_Device_B', 'Level normal - tandon', 59],
    // 3-7 hari lalu
    [50, 'ESP32_001', 'Data historis', 72],
    [80, 'ESP32_002', 'Data historis', 96],
    [30, 'ESP32_003', 'Data historis', 120],
    [92, 'IoT_Device_A', 'Data historis', 144],
    [58, 'IoT_Device_B', 'Data historis', 168],
];

$sql_water = "INSERT INTO water_level_readings (water_level, device_id, notes, timestamp) VALUES (:level, :device, :notes, :timestamp)";
$stmt_water = $pdo->prepare($sql_water);

$inserted_water = 0;
foreach ($water_data as $data) {
    $timestamp_str = date('Y-m-d H:i:s', time() - ($data[3] * 3600));
    
    try {
        $stmt_water->execute([
            ':level' => $data[0],
            ':device' => $data[1],
            ':notes' => $data[2],
            ':timestamp' => $timestamp_str
        ]);
        $inserted_water++;
        echo "  [{$inserted_water}/20] Level: {$data[0]}cm | Device: {$data[1]}\n";
    } catch (PDOException $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Seeding completed!\n\n";

// ==============================================
// 5. SUMMARY
// ==============================================
echo "Summary:\n";
echo "  - Device status: $inserted_devices/5 inserted\n";
echo "  - pH readings: $inserted_ph/20 inserted\n";
echo "  - Water level readings: $inserted_water/20 inserted\n\n";

// Get actual counts from database
$count_devices = $pdo->query("SELECT COUNT(*) FROM device_status")->fetchColumn();
$count_ph = $pdo->query("SELECT COUNT(*) FROM ph_readings")->fetchColumn();
$count_water = $pdo->query("SELECT COUNT(*) FROM water_level_readings")->fetchColumn();

echo "Total in database:\n";
echo "  - device_status: $count_devices rows\n";
echo "  - ph_readings: $count_ph rows\n";
echo "  - water_level_readings: $count_water rows\n\n";

// ==============================================
// 6. SHOW LATEST DATA
// ==============================================
echo str_repeat("=", 60) . "\n";
echo "Latest Data Sample:\n\n";

echo "📱 Device Status (latest 3):\n";
$latest_devices = $pdo->query("SELECT device_id, mode, battery_level, signal_strength FROM device_status ORDER BY last_seen DESC LIMIT 3")->fetchAll();
foreach ($latest_devices as $row) {
    echo "  - {$row['device_id']}: {$row['mode']}, Battery: {$row['battery_level']}%, Signal: {$row['signal_strength']} dBm\n";
}

echo "\n🧪 pH Readings (latest 3):\n";
$latest_ph = $pdo->query("SELECT ph_value, device_id, timestamp FROM ph_readings ORDER BY timestamp DESC LIMIT 3")->fetchAll();
foreach ($latest_ph as $row) {
    echo "  - pH: {$row['ph_value']} | Device: {$row['device_id']} | Time: {$row['timestamp']}\n";
}

echo "\n💧 Water Level (latest 3):\n";
$latest_water = $pdo->query("SELECT water_level, device_id, timestamp FROM water_level_readings ORDER BY timestamp DESC LIMIT 3")->fetchAll();
foreach ($latest_water as $row) {
    echo "  - Level: {$row['water_level']}cm | Device: {$row['device_id']} | Time: {$row['timestamp']}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 All done! Database is ready for use.\n";
echo "\nNext steps:\n";
echo "  - Test API: curl http://localhost/api/get-latest.php\n";
echo "  - View in browser: http://localhost/check_db.php\n";
echo "\n";
?>
