# Deploying to Azure VM (Linux)

Follow these steps to deploy your **Web IoT Receiver** to your Azure VM.

## 1. Preparing the VM

You are already logged in to your VM (`mario@iot-bridge`).

## 2. Uploading Files

Since you are on Windows and the VM is remote, the easiest way to get your files there is using **Git** or **Zip upload** (SCP).

### Option A: Using Git (Recommended)

1.  **On your Local Machine (VS Code):**
    Make sure your changes are committed and pushed to a repository (GitHub/GitLab).
    ```powershell
    git add .
    git commit -m "Ready for deploy"
    git push origin main
    ```

2.  **On the Azure VM:**
    Clone the repository.
    ```bash
    cd ~
    git clone <YOUR_REPO_URL> web-iot-receiver
    cd web-iot-receiver
    ```

### Option B: Using Zip/SCP (Direct Upload)

1.  **On the Azure VM:**
    Install zip/unzip just in case.
    ```bash
    sudo apt update
    sudo apt install unzip
    ```

2.  **On your Local Machine (PowerShell):**
    Zip your project (exclude `.git`, `.env`, `vendor` if possible to save space, but `vendor` is fine for simple PHP).
    ```powershell
    Compress-Archive -Path .\* -DestinationPath .\deploy.zip -Force
    scp .\deploy.zip mario@<VM_PUBLIC_IP>:/home/mario/
    ```

3.  **On the Azure VM:**
    Unzip it.
    ```bash
    unzip deploy.zip -d web-iot-receiver
    cd web-iot-receiver
    ```

## 3. Running the Setup Script

Once your files are in a folder (e.g., `~/web-iot-receiver`) on the VM:

1.  Make the script executable:
    ```bash
    chmod +x setup-ubuntu.sh
    ```

2.  Run the script with `sudo`:
    ```bash
    sudo ./setup-ubuntu.sh
    ```

This script will:
-   Install Nginx, PHP, MariaDB.
-   Create a database `iot_database` and user `iot_user`.
-   Import your `create_table_mysql.sql`.
-   Configure Nginx to serve your site.
-   Generate a `.env` file with the new database password.

## 4. Verification

After the script finishes, open your browser and visit:
`http://<YOUR_VM_PUBLIC_IP>/`

You should see your application running.

### Debugging
If something goes wrong:
-   **Nginx Logs:** `sudo tail -f /var/log/nginx/error.log`
-   **Check file permissions:** `ls -la /var/www/html/web-iot-receiver`
-   **Check DB Connection:** `cat /var/www/html/web-iot-receiver/.env`
