# Quick Guide: Seeding Database Neon

## 🎯 Apa yang Sudah Disiapkan

File `seed_manual.sql` sudah di-update dengan **3 tabel lengkap**:

| Tabel | Jumlah Data | Keterangan |
|-------|-------------|------------|
| `device_status` | 5 devices | Status perangkat IoT (mode, baterai, sinyal) |
| `ph_readings` | 20 records | Data pH dari kolam/sawah (7 hari terakhir) |
| `water_level_readings` | 20 records | Data ketinggian air (7 hari terakhir) |

**Total: 45 records** dengan timestamp yang realistis.

---

## 📝 Cara Menjalankan Seeding

### Step 1: Login ke Neon Console

1. Buka [console.neon.tech](https://console.neon.tech)
2. Login dengan akun Anda
3. Pilih project database IoT Anda

### Step 2: Buka SQL Editor

1. Di sidebar, klik **"SQL Editor"**
2. Atau klik tab **"Query"**

### Step 3: Copy & Paste Script

1. Buka file `seed_manual.sql` yang sudah di-update
2. **Select All** (Ctrl+A) → **Copy** (Ctrl+C)
3. Paste di Neon SQL Editor

### Step 4: Run Script

1. Klik tombol **"Run"** atau tekan **F5**
2. Tunggu hingga selesai (biasanya < 5 detik)

### Step 5: Verifikasi

Scroll ke bawah hasil query, Anda akan melihat:

**Count per tabel:**
```
device_status: 5
ph_readings: 20
water_level_readings: 20
```

**Data terakhir dari setiap tabel:**
- Latest device status
- Latest pH readings
- Latest water level readings

**Analytics:**
- Average pH per device
- Average water level per device
- Combined view semua data

---

## ✅ Expected Results

### Device Status (5 devices)

| device_id | mode | battery | signal | firmware |
|-----------|------|---------|--------|----------|
| ESP32_001 | active | 87.5% | -45 dBm | v1.2.3 |
| ESP32_002 | active | 92.0% | -52 dBm | v1.2.3 |
| ESP32_003 | sleep | 45.3% | -68 dBm | v1.2.2 |
| IoT_Device_A | active | 78.8% | -38 dBm | v1.3.0 |
| IoT_Device_B | maintenance | 100.0% | -42 dBm | v1.3.0 |

### pH Readings (20 records)

- pH range: 6.4 - 7.6
- Devices: 5 berbeda
- Timespan: 7 hari terakhir
- Notes: Deskriptif per lokasi

### Water Level Readings (20 records)

- Level range: 28 - 95 cm
- Devices: 5 berbeda
- Timespan: 7 hari terakhir
- Notes: Deskriptif per lokasi

---

## 🔍 Query Berguna Setelah Seeding

### Check jumlah data:
```sql
SELECT COUNT(*) FROM device_status;
SELECT COUNT(*) FROM ph_readings;
SELECT COUNT(*) FROM water_level_readings;
```

### Lihat data terbaru:
```sql
SELECT * FROM device_status ORDER BY last_seen DESC;
SELECT * FROM ph_readings ORDER BY timestamp DESC LIMIT 10;
SELECT * FROM water_level_readings ORDER BY timestamp DESC LIMIT 10;
```

### Analytics:
```sql
-- Average pH per device
SELECT device_id, ROUND(AVG(ph_value)::numeric, 2) as avg_ph
FROM ph_readings
GROUP BY device_id;

-- Device dengan battery rendah
SELECT device_id, battery_level
FROM device_status
WHERE battery_level < 50
ORDER BY battery_level ASC;
```

---

## 🚀 Setelah Seeding Berhasil

1. ✅ Database siap untuk API testing
2. ✅ Bisa test endpoint `/api/get-latest.php`
3. ✅ Bisa connect ESP32 untuk kirim data real
4. ✅ Bisa build dashboard/frontend

---

## 📁 File Penting

- `seed_manual.sql` - Script seeding lengkap (UPDATED)
- `create_device_status_table.sql` - SQL untuk buat tabel device_status
- `check_db.php` - Web viewer untuk cek database
- `config.php` - Konfigurasi database (sudah support Neon)

---

**Happy seeding! 🌱**
