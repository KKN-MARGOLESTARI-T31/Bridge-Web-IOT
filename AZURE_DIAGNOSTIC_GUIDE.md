# Quick Deployment Guide - Azure Diagnostics

## Step 1: Upload Diagnostic Script to Azure VM

### Option A: Using Git (Recommended)
```bash
# On local machine - commit and push
git add azure_diagnostics.php
git commit -m "Add Azure diagnostic script"
git push origin main

# On Azure VM - pull latest changes
ssh mario@<YOUR_AZURE_IP>
cd /var/www/html
sudo git pull origin main
```

### Option B: Using SCP (Direct Upload)
```powershell
# On local machine (PowerShell)
scp c:\Users\ASUS\Documents\Belajar\IOT-HTTP\web-iot-receiver\azure_diagnostics.php mario@<YOUR_AZURE_IP>:/tmp/

# On Azure VM
ssh mario@<YOUR_AZURE_IP>
sudo mv /tmp/azure_diagnostics.php /var/www/html/
sudo chown www-data:www-data /var/www/html/azure_diagnostics.php
```

## Step 2: Run Diagnostic

```bash
# SSH to Azure VM
ssh mario@<YOUR_AZURE_IP>

# Navigate to web directory
cd /var/www/html

# Run diagnostic script
php azure_diagnostics.php

# The output will show:
# - PHP version and available drivers
# - PostgreSQL extension status
# - .env file status
# - Database connection test
# - Network connectivity
# - Specific fix recommendations
```

## Step 3: Copy and Share Output

Copy the entire output from the diagnostic script and share it. Based on the results, I'll provide the exact fix commands.

## Common Fixes (Will be confirmed after diagnostic)

### If PostgreSQL driver is missing:
```bash
sudo apt update
sudo apt install php8.1-pgsql php8.1-mbstring
sudo systemctl restart apache2
```

### If .env file is missing:
```bash
cd /var/www/html
sudo nano .env
# Paste DATABASE_URL
sudo chmod 644 .env
sudo chown www-data:www-data .env
```

## Troubleshooting

If you get "permission denied":
```bash
# Check current directory
pwd

# Should be in /var/www/html
# If script exists but can't run:
sudo chmod 644 azure_diagnostics.php
```

---

**Ready to proceed?** Upload the script and run the diagnostic, then share the output!
