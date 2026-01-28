<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web IoT Receiver - Status</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; color: #333; }
        h1 { color: #0070f3; border-bottom: 2px solid #eaeaea; pb: 10px; }
        .card { border: 1px solid #eaeaea; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .status { font-weight: bold; }
        .success { color: #2e7d32; background: #e8f5e9; padding: 5px 10px; border-radius: 4px; display: inline-block; }
        .error { color: #c62828; background: #ffebee; padding: 5px 10px; border-radius: 4px; display: inline-block; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 4px; font-family: monospace; }
        ul { padding-left: 20px; }
        li { margin-bottom: 10px; }
        .endpoint { font-weight: bold; color: #0070f3; }
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
            echo '<span class="status success">CONNECTED (MySQL) ✅</span>';
        } else {
            echo '<span class="status error">FAILED ❌</span>';
        }
        ?>
        </p>

        <p>MySQL Driver: 
        <?php
        $drivers = PDO::getAvailableDrivers();
        if (in_array('mysql', $drivers)) {
            echo '<span class="status success">INSTALLED ✅</span>';
        } else {
            echo '<span class="status error">MISSING ❌</span> (PDO_MYSQL)';
        }
        ?>
        </p>
    </div>

    <div class="card">
        <h2>Available Endpoints</h2>
        <ul>
            <li>
                <span class="endpoint">POST /input.php</span><br>
                Endpoint utama untuk ESP32 (Form Data).<br>
                Author: <code>ESP32</code>
            </li>
            <li>
                <span class="endpoint">GET /api/get-latest.php</span><br>
                Untuk melihat data JSON terakhir.
            </li>
            <li>
                <span class="endpoint">POST /api/save-ph.php</span><br>
                Endpoint khusus data pH (JSON).
            </li>
        </ul>
    </div>

    <div class="card">
        <h2>Recent Data (Monitoring Logs)</h2>
        <?php
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 5");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($rows) > 0) {
                    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
                    echo "<thead><tr><th>Time</th><th>pH</th><th>Battery</th><th>Location</th></tr></thead>";
                    echo "<tbody>";
                    foreach ($rows as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "<td>" . $row['ph_value'] . "</td>";
                        echo "<td>" . $row['battery_level'] . "%</td>";
                        echo "<td>" . $row['location'] . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
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
