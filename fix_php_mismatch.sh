#!/bin/bash
# fix_php_mismatch.sh - Fix CLI vs Web Server PHP mismatch

echo "=== PHP CLI vs Web Server Diagnostic ==="
echo ""

# 1. Check CLI PHP version
echo "1. CLI PHP Version:"
php -v | head -n 1
echo ""

# 2. Check which php.ini CLI is using
echo "2. CLI php.ini location:"
php -i | grep "Loaded Configuration File"
echo ""

# 3. Check Apache PHP version
echo "3. Checking Apache PHP module..."
apache2ctl -M | grep php || echo "No PHP module found in Apache"
echo ""

# 4. List installed PHP packages
echo "4. Installed PHP packages:"
dpkg -l | grep php | grep -E "(pgsql|mysql)" | awk '{print $2, $3}'
echo ""

# 5. Check if pdo_pgsql is enabled in CLI
echo "5. CLI PDO drivers:"
php -m | grep -E "(pdo_pgsql|pdo_mysql)"
echo ""

# 6. Reinstall PHP PostgreSQL extension
echo "6. Reinstalling PHP8.1 PostgreSQL extensions..."
sudo apt update
sudo apt install --reinstall php8.1-pgsql php8.1-mysql -y
echo ""

# 7. Restart Apache
echo "7. Restarting Apache..."
sudo systemctl restart apache2
echo "✅ Apache restarted"
echo ""

# 8. Verify via web
echo "8. Testing via web (you should see output below):"
echo "   Visit: http://YOUR_IP/test_connection.php"
echo ""

echo "=== Fix Complete ==="
echo ""
echo "Next steps:"
echo "1. Visit: http://YOUR_IP/phpinfo_web.php"
echo "2. Search for 'pdo_pgsql' to verify it's loaded"
echo "3. Run: php test_connection.php (should now work)"
