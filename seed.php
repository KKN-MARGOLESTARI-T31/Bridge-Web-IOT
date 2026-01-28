<?php
// seed.php - Script seeding untuk MySQL
require_once 'config.php';

echo "Memulai seeding MySQL...\n";

$locations = ['sawah', 'sumur', 'kolam', 'sungai', 'tandon'];
$jumlah_data = 20;

try {
    // MySQL uses ?, ?, ? syntax usually, but PDO supports named parameters too if enabled or emulated.
    // However, keeping it standard PDO named params is fine for MySQL too.
    $sql = "INSERT INTO monitoring_logs (ph_value, battery_level, location, created_at) VALUES (:ph, :bat, :loc, :created)";
    $stmt = $pdo->prepare($sql);
    
    $inserted = 0;
    for ($i = 0; $i < $jumlah_data; $i++) {
        $ph = rand(60, 85) / 10;
        $battery = rand(20, 100);
        $location = $locations[array_rand($locations)];
        
        $timestamp = time() - rand(0, 7 * 24 * 60 * 60);
        $created_at = date('Y-m-d H:i:s', $timestamp);
        
        $stmt->execute([
            ':ph' => $ph,
            ':bat' => $battery,
            ':loc' => $location,
            ':created' => $created_at
        ]);
        
        $inserted++;
        echo "[$inserted/$jumlah_data] Inserted: pH=$ph, Bat=$battery%, Loc=$location\n";
    }
    echo "\nSeeding Selesai (MySQL)!\n";
    
} catch (PDOException $e) {
    die("Error Seeding: " . $e->getMessage() . "\n");
}
?>
