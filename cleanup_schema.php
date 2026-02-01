<?php
// cleanup_schema.php
// Script untuk menghapus kolom yang tidak diperlukan dari tabel water_level_readings

require_once 'config.php';

try {
    echo "Starting schema cleanup...\n";

    // 1. Drop column 'location'
    echo "Dropping column 'location' from 'water_level_readings'...\n";
    $sql1 = "ALTER TABLE water_level_readings DROP COLUMN IF EXISTS location";
    $pdo->exec($sql1);
    echo "Column 'location' dropped (if it existed).\n";

    // 2. Drop column 'status'
    echo "Dropping column 'status' from 'water_level_readings'...\n";
    $sql2 = "ALTER TABLE water_level_readings DROP COLUMN IF EXISTS status";
    $pdo->exec($sql2);
    echo "Column 'status' dropped (if it existed).\n";

    // 3. Verify final columns
    echo "\nVerifying current columns in 'water_level_readings':\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'water_level_readings'
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }

    echo "\nCleanup completed successfully.\n";

} catch (PDOException $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}
?>
