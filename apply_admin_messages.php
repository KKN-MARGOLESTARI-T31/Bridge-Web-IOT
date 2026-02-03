<?php
// apply_admin_messages.php
// Script to create the admin_messages table
// Bypasses "EXPLAIN" errors in SQL editors

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "🔧 Creating table: admin_messages...\n";

$sqlFile = __DIR__ . '/create_admin_messages.sql';

if (!file_exists($sqlFile)) {
    die("❌ Error: SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

echo "Executing SQL...\n";
$output = psql_execute($sql);

echo "Output:\n$output\n";

if (strpos($output, 'ERROR') !== false) {
    // Check for common foreign key error
    if (strpos($output, 'relation "users" does not exist') !== false) {
        echo "\n⚠️  ERROR: Table 'users' does not exist.\n";
        echo "   Please create the 'users' table first (Prisma User model).\n";
    } else {
        echo "❌ Creation FAILED.\n";
    }
    exit(1);
}

echo "✅ Table 'admin_messages' created successfully (or already exists).\n";

// Verify
$verifySql = "SELECT column_name FROM information_schema.columns 
              WHERE table_name = 'admin_messages' LIMIT 5;";
$result = psql_fetch_value($verifySql);

if ($result) {
    echo "✅ Verification passed. Columns found.\n";
} else {
    echo "⚠️  Verification warning: Table might not have been created correctly.\n";
}
?>
