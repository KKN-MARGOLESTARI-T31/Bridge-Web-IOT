# 🚀 Quick Start - Neon Database Setup

Panduan cepat setup database Neon untuk proyek IoT Receiver dalam 5 menit!

## ✅ Checklist Setup

### Step 1: Buat Akun & Project Neon (2 menit)

- [ ] Buka [neon.tech](https://neon.tech)
- [ ] Klik **"Sign Up"** (gunakan GitHub atau Email)
- [ ] Klik **"Create a project"**
- [ ] Isi:
  - Project name: `iot-receiver`
  - PostgreSQL version: `16` (default)
  - Region: **Singapore** (`aws-ap-southeast-1`) ← Untuk Indonesia
- [ ] Klik **"Create Project"**

### Step 2: Copy Connection String (30 detik)

Setelah project dibuat, Anda akan lihat tampilan seperti ini:

```
┌─────────────────────────────────────────────────┐
│ Connection String                               │
├─────────────────────────────────────────────────┤
│ [Pooled]  [Direct]                              │
│                                                 │
│ postgresql://user:pass@ep-xxx.aws.neon.tech/.. │
│                                                 │
│ [📋 Copy]                                       │
└─────────────────────────────────────────────────┘
```

- [ ] Pilih tab **"Pooled"** (bukan Direct)
- [ ] Klik tombol **Copy** untuk copy connection string
- [ ] Simpan di clipboard

### Step 3: Setup File `.env` (1 menit)

**Windows (PowerShell):**
```powershell
cd C:\Users\ASUS\Documents\Belajar\IOT-HTTP\web-iot-receiver
Copy-Item .env.example .env
notepad .env
```

**Linux/Mac:**
```bash
cd /path/to/web-iot-receiver
cp .env.example .env
nano .env
```

Edit file `.env`, paste connection string yang tadi di-copy:

```env
# =====================================================
# Option 2: PostgreSQL - Neon (RECOMMENDED)
# =====================================================
DATABASE_URL=postgresql://xxxxx:xxxxxx@ep-xxxxx.aws.neon.tech/neondb?sslmode=require
```

**⚠️ PENTING:** Ganti semua `xxxxx` dengan connection string asli dari Neon!

- [ ] Copy `.env.example` → `.env`
- [ ] Paste connection string ke `.env`
- [ ] Save file

### Step 4: Install Ekstensi PostgreSQL (1 menit)

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install php-pgsql -y
sudo systemctl restart apache2
```

**Windows (XAMPP/Laragon):**
1. Cari file `php.ini` (biasanya di `C:\laragon\bin\php\php-8.x\php.ini`)
2. Cari baris `;extension=pgsql` dan `;extension=pdo_pgsql`
3. Hapus tanda `;` di depannya:
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```
4. Save dan restart Apache/Laragon

- [ ] Install/enable ekstensi `pgsql` dan `pdo_pgsql`
- [ ] Restart web server

### Step 5: Setup Database Tables (30 detik)

```bash
php setup_db_neon.php
```

Output yang diharapkan:
```
=== IoT Database Setup for Neon PostgreSQL ===

✓ File .env ditemukan
✓ DATABASE_URL terdeteksi

Connecting to:
  Host: ep-xxxxx.aws.neon.tech
  Port: 5432
  Database: neondb
  User: your_user
  SSL Mode: require

✓ Connected successfully!

Creating tables...

1. Creating table: ph_readings
   ✓ Table ph_readings created
2. Creating table: water_level_readings
   ✓ Table water_level_readings created

✅ Setup completed successfully!
```

- [ ] Jalankan `php setup_db_neon.php`
- [ ] Pastikan melihat `✅ Setup completed successfully!`

### Step 6: Test Connection (30 detik)

**Option A - Via Browser (Recommended):**
```
http://localhost/test_neon.php
```

Anda akan melihat halaman web dengan:
- ✅ Connection successful!
- Daftar tabel dan jumlah data
- PostgreSQL version info

**Option B - Via Command Line:**
```bash
php test_neon.php
```

- [ ] Buka `http://localhost/test_neon.php` di browser
- [ ] Pastikan melihat **"✅ Connection successful!"**

### Step 7 (Opsional): Seed Data untuk Testing

```bash
php seed.php
```

Ini akan mengisi database dengan 10 sample data untuk testing.

- [ ] Jalankan `php seed.php` (opsional)

---

## 🎉 Selesai!

Database Neon Anda sudah siap digunakan!

### Test API Endpoints

**Test simpan data pH:**
```bash
curl -X POST http://localhost/api/save-ph.php \
  -H "Content-Type: application/json" \
  -d '{"value":7.5,"location":"kolam","deviceId":"ESP32_001"}'
```

**Test ambil data terbaru:**
```bash
curl http://localhost/api/get-latest.php
```

**Test dari ESP32:**
```cpp
// Arduino/ESP32 Code
HTTPClient http;
http.begin("http://your-server.com/input.php");
http.addHeader("Content-Type", "application/x-www-form-urlencoded");

String postData = "ph=7.2&battery=85.5&location=sawah";
int httpCode = http.POST(postData);

if (httpCode == 200) {
  Serial.println("Data terkirim!");
}
```

---

## 🆘 Troubleshooting

### ❌ Error: "could not find driver"
**Solusi:** Install ekstensi PostgreSQL (lihat Step 4)

### ❌ Error: "DATABASE_URL tidak ditemukan"
**Solusi:** Pastikan file `.env` ada dan berisi `DATABASE_URL=...`

### ❌ Error: "Connection failed: FATAL: password authentication failed"
**Solusi:** 
1. Copy ulang connection string dari Neon Dashboard
2. Pastikan tidak ada spasi atau karakter tersembunyi
3. Pastikan ada `?sslmode=require` di akhir URL

### ❌ Error: "SSL connection required"
**Solusi:** Tambahkan `?sslmode=require` di akhir connection string

---

## 📚 Resources

- 📖 Panduan lengkap: [SETUP_NEON.md](SETUP_NEON.md)
- 🌐 Neon Console: [console.neon.tech](https://console.neon.tech)
- 📝 Neon Documentation: [neon.tech/docs](https://neon.tech/docs)
- 🏠 Project README: [README.md](README.md)

---

## 💡 Tips

1. **Gratis selamanya**: Neon free tier cukup untuk IoT project kecil-menengah
2. **Auto-suspend**: Database akan tidur otomatis saat tidak digunakan (hemat resource)
3. **Branching**: Bisa bikin copy database untuk testing tanpa ganggu production
4. **Monitoring**: Cek usage di Dashboard → Metrics
5. **Backup**: Neon otomatis backup data Anda

**Selamat coding! 🚀**
