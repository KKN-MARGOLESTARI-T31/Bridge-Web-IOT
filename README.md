# Web IoT Receiver (PHP Native)

Aplikasi web PHP sederhana untuk menerima dan menyimpan data dari perangkat IoT (ESP32/Arduino) ke database PostgreSQL.

## Struktur Project

```
web-iot-receiver/
├── config.php                        # Konfigurasi koneksi database (MySQL)
├── input.php                         # PHP Bridge (MySQLi)
├── create_table_mysql.sql            # SQL untuk MySQL
├── api/
│   ├── save-ph.php                   # Endpoint untuk menyimpan data pH (JSON)
│   ├── save-water-level.php          # Endpoint untuk menyimpan data ketinggian air (JSON)
│   └── get-latest.php                # Endpoint untuk mengambil data terbaru
└── README.md
```

## Persyaratan Server

- PHP 7.4 atau lebih baru
- Ekstensi PHP: 
  - Untuk MySQL: `mysqli`, `pdo_mysql`, `json`
  - Untuk PostgreSQL: `pgsql`, `pdo_pgsql`, `json`
- Web Server (Apache/Nginx/IIS - Laragon Recommended)
- Database: MySQL / MariaDB / **PostgreSQL (Neon - Recommended)**

## Setup Database

### Option 1: PostgreSQL - Neon (RECOMMENDED) ⭐

**Neon** adalah serverless PostgreSQL yang gratis dan mudah digunakan.

**Quick Setup:**

1. **Sign up** di [neon.tech](https://neon.tech)
2. **Create project** dan pilih region terdekat (Singapore untuk Indonesia)
3. **Copy connection string** (gunakan "Pooled connection")
4. **Setup file `.env`:**
   ```bash
   cp .env.example .env
   # Edit .env dan isi DATABASE_URL dengan connection string dari Neon
   ```
5. **Install ekstensi PostgreSQL:**
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-pgsql
   sudo systemctl restart apache2
   ```
6. **Setup tables:**
   ```bash
   php setup_db_neon.php
   ```
7. **Test connection:**
   ```bash
   php test_neon.php
   # atau buka di browser: http://localhost/test_neon.php
   ```

📖 **Panduan lengkap:** Lihat [SETUP_NEON.md](SETUP_NEON.md)

### Option 2: MySQL (Local/Traditional Hosting)

1. Buat database bernama `iot_database` (atau sesuaikan di `.env`).
2. Import script SQL: `create_table_mysql.sql`.
   - Bisa via phpMyAdmin -> Import.
   - Atau biarkan aplikasi membuat tabel otomatis (jika ada script setup).

## Cara Penggunaan (API Endpoints)

### 0. PHP Bridge untuk ESP32 (Recommended untuk IoT Devices)

**URL:** `/input.php`  
**Method:** `POST`  
**Content-Type:** `application/x-www-form-urlencoded` (Form-Data)  
**Parameters:**

- `ph` (float) - Nilai pH
- `battery` (float) - Level baterai (%)
- `location` (string) - Lokasi pengukuran (sawah, sumur, kolam)

**Contoh dari ESP32:**

```cpp
// Arduino/ESP32 Code
HTTPClient http;
http.begin("http://yourhosting.com/input.php");
http.addHeader("Content-Type", "application/x-www-form-urlencoded");

String postData = "ph=7.2&battery=85.5&location=sawah";
int httpCode = http.POST(postData);
String response = http.getString();
Serial.println(response); // "Sukses Masuk Neon!"
http.end();
```

**Testing dengan cURL:**

```bash
curl -X POST http://localhost/web-iot-receiver/input.php \
  -d "ph=7.2&battery=85.5&location=sawah"
```

### 1. Simpan Data pH

**URL:** `/api/save-ph.php`
**Method:** `POST`
**Body (JSON):**

```json
{
  "value": 7.2,
  "location": "kolam",
  "deviceId": "ESP32_001",
  "temperature": 28.5
}
```

### 2. Simpan Data Water Level

**URL:** `/api/save-water-level.php`
**Method:** `POST`
**Body (JSON):**

```json
{
  "level": 45.5,
  "location": "sawah",
  "deviceId": "ESP32_001",
  "status": "normal"
}
```

### 3. Ambil Data Terbaru

**URL:** `/api/get-latest.php`
**Method:** `GET`

## Testing dengan cURL

```bash
# Test simpan pH
curl -X POST http://localhost/web-iot-receiver/api/save-ph.php \
  -H "Content-Type: application/json" \
  -d '{"value":7.5,"location":"kolam","deviceId":"TEST_001"}'

# Test simpan Water Level
curl -X POST http://localhost/web-iot-receiver/api/save-water-level.php \
  -H "Content-Type: application/json" \
  -d '{"level":50.0,"location":"sawah","deviceId":"TEST_001"}'

# Test ambil data
curl http://localhost/web-iot-receiver/api/get-latest.php
```
