<?php
// fix_schema_dependency.php
// Creates missing 'users' table then 'admin_messages'

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config_psql.php';

echo "🔧 Fixing Schema Dependencies...\n\n";

// 1. Create Users Table
echo "1️⃣  Creating 'users' table (required for Foreign Key)...\n";
$sqlUsers = file_get_contents(__DIR__ . '/create_users_minimal.sql');
$out1 = psql_execute($sqlUsers);
echo $out1 . "\n";

// 2. Create Admin Messages Table
echo "2️⃣  Creating 'admin_messages' table...\n";
$sqlMsg = file_get_contents(__DIR__ . '/create_admin_messages.sql');
$out2 = psql_execute($sqlMsg);
echo $out2 . "\n";

// Verify
echo "\n🔍 Verification:\n";
$check = psql_fetch_value("SELECT COUNT(*) FROM information_schema.tables WHERE table_name IN ('users', 'admin_messages')");
echo "Tables found matching (users, admin_messages): $check / 2\n";

if ($check >= 2) {
    echo "✅ SUCCESS! Both tables created.\n";
} else {
    echo "⚠️  Something is still missing.\n";
    exit(1);
}
?>
