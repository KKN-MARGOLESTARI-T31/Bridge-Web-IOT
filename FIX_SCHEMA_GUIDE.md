# 🔧 Fix Schema Database - Panduan Lengkap

## ⚠️ Masalah

Schema database yang dibuat **tidak sesuai** dengan spesifikasi yang benar. Perlu di-fix!

## ✅ Solusi: 2 Langkah Mudah

### **Step 1: Drop & Create Schema yang Benar**

1. Login ke [console.neon.tech](https://console.neon.tech)
2. Pilih project Anda
3. Klik **SQL Editor**
4. Copy-paste **seluruh isi** file `fix_schema.sql`
5. Klik **Run**
6. ✅ Done! Table lama terhapus, table baru dengan schema benar dibuat

### **Step 2: Seed Data**

1. Masih di SQL Editor
2. Copy-paste **seluruh isi** file `seed_correct.sql`
3. Klik **Run**
4. ✅ Done! Database terisi 41 records:
   - 1 device status
   - 20 pH readings (kolam & sawah)
   - 20 water level readings (kolam & sawah)

---

## 📊 Schema yang Benar

### 1. **device_status**
```sql
id            VARCHAR(50) PRIMARY KEY
activeMode    VARCHAR(20)  -- 'sawah' atau 'kolam'
battery       DECIMAL(5,2)
signal        INTEGER
lastUpdate    TIMESTAMP
```

### 2. **ph_readings**
```sql
id           VARCHAR(50) PRIMARY KEY
value        DECIMAL(4,2)  -- Nilai pH
location     VARCHAR(20)   -- 'kolam' atau 'sawah'
timestamp    TIMESTAMP
deviceId     VARCHAR(50)   -- Optional
temperature  DECIMAL(5,2)  -- Optional
```

### 3. **water_level_readings**
```sql
id          VARCHAR(50) PRIMARY KEY
level       DECIMAL(8,2)  -- Ketinggian air
location    VARCHAR(20)   -- 'kolam' atau 'sawah'
timestamp   TIMESTAMP
status      VARCHAR(20)   -- 'low', 'normal', 'high', 'critical'
```

---

## 🎯 Expected Results

Setelah selesai, verify dengan query:

```sql
-- Check row counts
SELECT 'device_status' as table, COUNT(*) FROM device_status
UNION ALL
SELECT 'ph_readings', COUNT(*) FROM ph_readings
UNION ALL
SELECT 'water_level_readings', COUNT(*) FROM water_level_readings;
```

Hasil yang diharapkan:
```
device_status: 1
ph_readings: 20
water_level_readings: 20
```

---

## 🚀 Setelah Fix

Database siap digunakan:
- ✅ Schema sesuai spesifikasi
- ✅ Data sample tersedia
- ✅ API bisa langsung digunakan
- ✅ ESP32 bisa kirim data

---

**Total waktu: ~2 menit** ⚡
