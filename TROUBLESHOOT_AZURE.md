# Quick Fix: Database Connection Failed di Azure

## 🔍 Diagnosis Cepat

Error "Database connection failed" biasanya karena:
1. File `.env` tidak ter-load
2. Path `.env` salah
3. Permissions issue

---

## ✅ Quick Fix Commands

SSH ke Azure VM dan jalankan:

```bash
# 1. Cek file .env ada atau tidak
cd /var/www/html
ls -la .env
cat .env

# 2. Jika .env TIDAK ADA atau KOSONG, buat ulang:
nano .env
```

Paste connection string:
```env
DATABASE_URL=postgresql://neondb_owner:npg_n9D8AOwHoixu@ep-snowy-butterfly-a12pm14d-pooler.ap-southeast-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require
```

Save: `Ctrl+O`, Enter, `Ctrl+X`

```bash
# 3. Set permissions
sudo chmod 644 .env
sudo chown www-data:www-data .env

# 4. Test koneksi
php debug_db.php
```

---

## 🔧 Jika Masih Gagal

Cek apakah `config.php` load `.env` dengan benar:

```bash
# Test load .env
php -r "
\$envFile = '/var/www/html/.env';
if (file_exists(\$envFile)) {
    echo 'File .env exists\n';
    \$lines = file(\$envFile);
    foreach (\$lines as \$line) {
        if (strpos(\$line, 'DATABASE_URL') !== false) {
            echo 'DATABASE_URL found\n';
        }
    }
} else {
    echo 'File .env NOT FOUND\n';
}
"
```

---

## 📋 Expected Output

Setelah fix, output `php debug_db.php` harus:
```
Loading env from: /var/www/html/.env
Raw URL found: YES
Available Drivers: mysql, pgsql
Connection Success!
```

---

**Kemungkinan Besar:** File `.env` tidak ada atau tidak di-load oleh web server (berbeda dengan CLI).

**Solusi:** Pastikan `.env` ada di `/var/www/html/.env` dengan permissions yang benar.
