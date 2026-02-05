<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web IoT Receiver - Status</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; line-height: 1.6; color: #333; }
        h1 { color: #0070f3; border-bottom: 2px solid #eaeaea; pb: 10px; }
        .card { border: 1px solid #eaeaea; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .status { font-weight: bold; }
        .success { color: #2e7d32; background: #e8f5e9; padding: 5px 10px; border-radius: 4px; display: inline-block; }
        .error { color: #c62828; background: #ffebee; padding: 5px 10px; border-radius: 4px; display: inline-block; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 4px; font-family: monospace; }
        ul { padding-left: 20px; }
        li { margin-bottom: 10px; }
        .endpoint { font-weight: bold; color: #0070f3; }
        table { border-collapse: collapse; width: 100%; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
    </style>
</head>
<body>
    <h1>Web IoT Receiver Status</h1>

    <div class="card">
        <h2>System Health</h2>
        <p>PHP Version: <strong><?php echo phpversion(); ?></strong></p>
        
        <p>Database Connection: 
        <?php
        require_once 'config.php';
        if (isset($pdo)) {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            $driver_label = strtoupper($driver);
            echo '<span class="status success">CONNECTED (' . $driver_label . ') ✅</span>';
        } else {
            echo '<span class="status error">FAILED ❌</span>';
        }
        ?>
        </p>

        <p>Available PDO Drivers: 
        <?php
        $drivers = PDO::getAvailableDrivers();
        echo '<span class="status success">' . implode(', ', $drivers) . '</span>';
        ?>
        </p>
        
        <p>PostgreSQL Driver: 
        <?php
        if (in_array('pgsql', $drivers)) {
            echo '<span class="status success">INSTALLED ✅</span>';
        } else {
            echo '<span class="status error">MISSING ❌</span> (PDO_PGSQL)';
        }
        ?>
        </p>
    </div>

    <div class="card">
        <h2>Available Endpoints</h2>
        <ul>
            <li>
                <span class="endpoint">POST /input.php</span><br>
                Main IoT data ingestion endpoint (JSON or Form Data).<br>
                Accepts: <code>ph, battery, level, temperature, signal, deviceId</code>
            </li>
            <li>
                <span class="endpoint">GET /read_database_structure.php</span><br>
                View actual database schema and structure.
            </li>
        </ul>
    </div>

    <div class="card">
        <h2>Pump Control Status</h2>
        <?php
        if (isset($pdo)) {
            try {
                // Get latest pump command from device_controls (Official Command Status)
                $stmt = $pdo->query('SELECT id, "deviceId", mode, command, "updatedAt", "createdAt", "actionBy", reason 
                                     FROM device_controls 
                                     WHERE mode = \'PUMP\' 
                                     ORDER BY "updatedAt" DESC LIMIT 1');
                $pumpData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($pumpData) {
                    $command = strtoupper($pumpData['command']); // Ensure uppercase
                    $deviceId = $pumpData['deviceId'] ?? 'N/A';
                    $updatedAt = $pumpData['updatedAt'];
                    $actionBy = $pumpData['actionBy'] ?? 'System';
                    
                    // Display status based on command
                    if ($command === 'ON' || $command === 'TRUE') {
                        echo '<div style="background:#e8f5e9; padding:15px; border-radius:8px; border-left: 5px solid #2e7d32;">';
                        echo '<h3 style="margin:0; color:#2e7d32;">✅ POMPA MENYALA (ON)</h3>';
                        echo '<p style="margin:5px 0 0;">Last Command: <strong>ON</strong></p>';
                        echo '</div>';
                    } else {
                        echo '<div style="background:#ffebee; padding:15px; border-radius:8px; border-left: 5px solid #c62828;">';
                        echo '<h3 style="margin:0; color:#c62828;">❌ POMPA MATI (OFF)</h3>';
                        echo '<p style="margin:5px 0 0;">Last Command: <strong>OFF</strong></p>';
                        echo '</div>';
                    }
                    
                    echo '<p style="margin-top:10px; font-size:0.9em; color:#666;">
                            <strong>Device:</strong> ' . htmlspecialchars($deviceId) . ' | 
                            <strong>Updated:</strong> ' . $updatedAt . ' |
                            <strong>By:</strong> ' . htmlspecialchars($actionBy) . '
                          </p>';

                } else {
                    echo '<p class="error">⚠️ Belum ada status pompa.</p>';
                    echo '<p>Sistem menunggu data pertama dari ESP32...</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error mengambil status pump: ' . $e->getMessage() . '</p>';
            }
        }
        ?>
    </div>

    <div class="card">
        <h2>All Monitoring Data</h2>
        <?php
        if (isset($pdo)) {
            try {
                // Query with actual production schema columns
                // NOTE: "deviceId" must be quoted because it's case-sensitive in PostgreSQL
                $stmt = $pdo->query('SELECT id, battery_level, ph_value, level, temperature, signal_strength, created_at, "deviceId", pump_status FROM monitoring_logs ORDER BY created_at DESC');
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($rows) > 0) {
                    echo "<p><strong>Total Records: " . count($rows) . "</strong></p>";
                    echo "<div style='overflow-x: auto;'>";
                    echo "<table>";
                    echo "<thead><tr><th>Device ID</th><th>Time</th><th>pH</th><th>Battery (%)</th><th>Level (cm)</th><th>Temp (°C)</th><th>Signal</th><th>Pump</th></tr></thead>";
                    echo "<tbody>";
                    foreach ($rows as $row) {
                        $pStatus = $row['pump_status'] ?? '-';
                        $pColor = ($pStatus === 'true' || $pStatus === 'ON') ? 'color:green;font-weight:bold;' : 'color:red;';
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['deviceId'] ?? '-') . "</td>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "<td>" . number_format($row['ph_value'], 2) . "</td>";
                        echo "<td>" . number_format($row['battery_level'], 1) . "%</td>";
                        echo "<td>" . number_format($row['level'], 1) . "</td>";
                        echo "<td>" . ($row['temperature'] ? number_format($row['temperature'], 1) : '-') . "</td>";
                        echo "<td>" . ($row['signal_strength'] ?? '-') . "</td>";
                        echo "<td style='" . $pColor . "'>" . htmlspecialchars($pStatus) . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                    echo "</div>";
                } else {
                    echo "<p>Belum ada data masuk.</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>Error mengambil data: " . $e->getMessage() . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>
