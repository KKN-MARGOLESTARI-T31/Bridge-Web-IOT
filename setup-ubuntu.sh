#!/bin/bash

# Abort on error
set -e

# Colors
GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}=== Starting Setup for IoT Receiver on Ubuntu ===${NC}"

# 1. Update Packages
echo -e "${GREEN}1. Updating system packages...${NC}"
sudo apt update && sudo apt upgrade -y

# 2. Install Dependencies
echo -e "${GREEN}2. Installing Nginx, MariaDB, PHP...${NC}"
sudo apt install -y nginx mariadb-server php-fpm php-mysql php-json php-mbstring unzip

# 3. Configure Database
echo -e "${GREEN}3. Setting up Database...${NC}"
DB_NAME="iot_database"
DB_USER="iot_user"
# Generate a random password if not provided
DB_PASS=$(openssl rand -base64 12)

echo "Creating database '${DB_NAME}' and user '${DB_USER}'..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo -e "${GREEN}Database setup complete!${NC}"
echo -e "DB Name: ${DB_NAME}"
echo -e "DB User: ${DB_USER}"
echo -e "DB Pass: ${DB_PASS}  <-- SAVE THIS!"

# Save credentials to a temp file for the user to see later
echo "DB_HOST=localhost" > .env.generated
echo "DB_NAME=${DB_NAME}" >> .env.generated
echo "DB_USER=${DB_USER}" >> .env.generated
echo "DB_PASS=${DB_PASS}" >> .env.generated

# 4. Import SQL
if [ -f "create_table_mysql.sql" ]; then
    echo -e "${GREEN}4. Importing SQL schema...${NC}"
    sudo mysql ${DB_NAME} < create_table_mysql.sql
    echo "Schema imported."
else
    echo "WARNING: create_table_mysql.sql not found. Skipping import."
fi

# 5. Configure Nginx
echo -e "${GREEN}5. Configuring Nginx...${NC}"
SITE_CONF="/etc/nginx/sites-available/iot-receiver"

# Detect PHP version
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo "Detected PHP version: $PHP_VERSION"

sudo bash -c "cat > ${SITE_CONF}" <<EOF
server {
    listen 80;
    server_name _; # Listen on all IPs

    root /var/www/html/web-iot-receiver;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Enable site
if [ ! -L "/etc/nginx/sites-enabled/iot-receiver" ]; then
    sudo ln -s ${SITE_CONF} /etc/nginx/sites-enabled/
fi

# Remove default if exists
if [ -f "/etc/nginx/sites-enabled/default" ]; then
    sudo rm /etc/nginx/sites-enabled/default
fi

# Test and Reload
sudo nginx -t
sudo systemctl reload nginx

# 6. Set Permissions
echo -e "${GREEN}6. Setting Permissions...${NC}"
TARGET_DIR="/var/www/html/web-iot-receiver"
# Create dir if not exists (assuming script is run from outside or we move files in)
if [ ! -d "$TARGET_DIR" ]; then
    sudo mkdir -p "$TARGET_DIR"
    echo "Created $TARGET_DIR"
fi

# Assuming the current directory contains the source files (because user will likely git clone or upload here)
# We will copy contents from current dir to /var/www/html/web-iot-receiver IF script is not inside it already
# But safer implies user should unzip or git clone into the target.
# Let's assume this script is inside the project folder.
echo "Copying files to $TARGET_DIR..."
sudo cp -r . "$TARGET_DIR"

# Set ownership to www-data
sudo chown -R www-data:www-data "$TARGET_DIR"
sudo chmod -R 755 "$TARGET_DIR"

# Create .env from generated credentials
echo -e "${GREEN}7. Creating .env file...${NC}"
sudo mv .env.generated "$TARGET_DIR/.env"
sudo chown www-data:www-data "$TARGET_DIR/.env"

echo -e "${GREEN}=== Setup Complete! ===${NC}"
echo "Your API is ready at: http://<YOUR-VM-IP>/"
echo "Database credentials saved in $TARGET_DIR/.env"
