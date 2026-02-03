# Schema Simplification Guide

## 📋 Changes

### Database Changes
1. ✅ DROP kolom `location` dari `monitoring_logs`
2. ✅ ADD kolom `level` ke `monitoring_logs`
3. ✅ DROP tabel `water_level_readings` (tidak dipakai)
4. ✅ Timezone Jakarta (GMT+7) diatur dari PHP server

### New Schema: monitoring_logs

| Column | Type | Description |
|--------|------|-------------|
| id | SERIAL | Auto-increment primary key |
| ph_value | DECIMAL(4,2) | Nilai pH air |
| battery_level | DECIMAL(5,2) | Level battery (%) |
| level | DECIMAL(10,2) | Ketinggian air (cm) |
| created_at | TIMESTAMP | Waktu data masuk (Jakarta GMT+7) |

## 🚀 Migration Steps

### Step 1: Run SQL Migration di Neon SQL Editor

Copy-paste file [`migrate_to_simple_schema.sql`](file:///C:/Users/ASUS/Documents/Belajar/IOT-HTTP/web-iot-receiver/migrate_to_simple_schema.sql) ke Neon SQL Editor dan execute.

### Step 2: Upload Updated Files ke Server

```bash
scp input.php your-username@20.2.138.40:/var/www/html/
scp check_database_data.php your-username@20.2.138.40:/var/www/html/
```

### Step 3: Update ESP32 Code

ESP32 sekarang tidak perlu kirim parameter `location` lagi.

**Old:**
```cpp
String postData = "ph=" + String(ph) + "&battery=" + String(battery) + 
                  "&location=sawah&level=" + String(level);
```

**New:**
```cpp
String postData = "ph=" + String(ph) + "&battery=" + String(battery) + 
                  "&level=" + String(level);
```

### Step 4: Test

1. Kirim data dari ESP32
2. Check database: `http://20.2.138.40/check_database_data.php`
3. Verify timestamp dalam GMT+7 Jakarta

## ✨ Benefits

- ✅ **Lebih Simple** - 1 tabel saja untuk semua data
- ✅ **Timestamp Akurat** - Server yang atur waktu (GMT+7)
- ✅ **Lebih Efisien** - Tidak perlu 2x INSERT
- ✅ **Mudah Query** - Semua data dalam 1 tabel

## 🔍 Verification

After migration, verify:

```sql
-- Check table structure
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs';

-- Test insert
INSERT INTO monitoring_logs (ph_value, battery_level, level, created_at) 
VALUES (7.0, 85.5, 15.3, NOW());

-- Verify data
SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 5;
```

## 📝 Notes

- Timezone set di `input.php`: `date_default_timezone_set('Asia/Jakarta')`
- Timestamp digenerate dengan: `date('Y-m-d H:i:s')`
- Waktu yang tersimpan adalah waktu Jakarta (GMT+7)
