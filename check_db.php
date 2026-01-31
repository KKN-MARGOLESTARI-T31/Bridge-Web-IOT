&lt;?php
// check_db.php - Simple database check untuk Neon
// Bisa diakses via browser: http://localhost/check_db.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Database Check&lt;/title&gt;
    &lt;style&gt;
        body { font-family: Arial; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #00D4AA; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #00D4AA; color: white; }
        .success { color: #00D4AA; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;";

echo "&lt;h1&gt;🔍 Database Check - Neon PostgreSQL&lt;/h1&gt;";

try {
    require_once 'config.php';
    
    echo "&lt;div class='box'&gt;
            &lt;h2&gt;✅ Connection Status&lt;/h2&gt;
            &lt;p class='success'&gt;Connected to Neon successfully!&lt;/p&gt;
          &lt;/div&gt;";
    
    // Get database info
    $version = $pdo->query("SELECT version()")->fetchColumn();
    echo "&lt;div class='box'&gt;
            &lt;h2&gt;📊 Database Info&lt;/h2&gt;
            &lt;p&gt;&lt;strong&gt;PostgreSQL Version:&lt;/strong&gt;&lt;/p&gt;
            &lt;pre&gt;" . htmlspecialchars($version) . "&lt;/pre&gt;
          &lt;/div&gt;";
    
    // Check tables
    $tables = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'BASE TABLE'
        ORDER BY table_name
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "&lt;div class='box'&gt;
            &lt;h2&gt;📋 Tables&lt;/h2&gt;
            &lt;table&gt;
                &lt;tr&gt;
                    &lt;th&gt;Table Name&lt;/th&gt;
                    &lt;th&gt;Row Count&lt;/th&gt;
                    &lt;th&gt;Actions&lt;/th&gt;
                &lt;/tr&gt;";
    
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "&lt;tr&gt;
                &lt;td&gt;$table&lt;/td&gt;
                &lt;td&gt;$count rows&lt;/td&gt;
                &lt;td&gt;&lt;a href='?view=$table'&gt;View Data&lt;/a&gt;&lt;/td&gt;
              &lt;/tr&gt;";
    }
    
    echo "    &lt;/table&gt;
          &lt;/div&gt;";
    
    // View specific table if requested
    if (isset($_GET['view']) && in_array($_GET['view'], $tables)) {
        $table = $_GET['view'];
        $data = $pdo->query("SELECT * FROM $table ORDER BY timestamp DESC LIMIT 10")->fetchAll();
        
        echo "&lt;div class='box'&gt;
                &lt;h2&gt;📄 Latest 10 records from: $table&lt;/h2&gt;";
        
        if (empty($data)) {
            echo "&lt;p&gt;No data yet. Run seeding: &lt;code&gt;php seed_neon.php&lt;/code&gt;&lt;/p&gt;";
        } else {
            echo "&lt;table&gt;&lt;tr&gt;";
            // Headers
            foreach (array_keys($data[0]) as $key) {
                echo "&lt;th&gt;" . htmlspecialchars($key) . "&lt;/th&gt;";
            }
            echo "&lt;/tr&gt;";
            
            // Rows
            foreach ($data as $row) {
                echo "&lt;tr&gt;";
                foreach ($row as $value) {
                    echo "&lt;td&gt;" . htmlspecialchars($value ?? 'NULL') . "&lt;/td&gt;";
                }
                echo "&lt;/tr&gt;";
            }
            echo "&lt;/table&gt;";
        }
        
        echo "&lt;/div&gt;";
    }
    
    echo "&lt;div class='box'&gt;
            &lt;h2&gt;🛠️ Actions&lt;/h2&gt;
            &lt;ul&gt;
                &lt;li&gt;&lt;a href='?'&gt;Refresh&lt;/a&gt;&lt;/li&gt;
                &lt;li&gt;Seed data: &lt;code&gt;php seed_neon.php&lt;/code&gt; (via terminal)&lt;/li&gt;
                &lt;li&gt;&lt;a href='test_neon.php'&gt;Test Connection (Detailed)&lt;/a&gt;&lt;/li&gt;
            &lt;/ul&gt;
          &lt;/div&gt;";
    
} catch (Exception $e) {
    echo "&lt;div class='box'&gt;
            &lt;h2&gt;❌ Connection Failed&lt;/h2&gt;
            &lt;p class='error'&gt;Error: " . htmlspecialchars($e->getMessage()) . "&lt;/p&gt;
            &lt;p&gt;Check:&lt;/p&gt;
            &lt;ul&gt;
                &lt;li&gt;File .env exists and has valid DATABASE_URL&lt;/li&gt;
                &lt;li&gt;PostgreSQL extension is enabled (pgsql, pdo_pgsql)&lt;/li&gt;
                &lt;li&gt;Connection string is correct&lt;/li&gt;
            &lt;/ul&gt;
          &lt;/div&gt;";
}

echo "&lt;/body&gt;&lt;/html&gt;";
?>
