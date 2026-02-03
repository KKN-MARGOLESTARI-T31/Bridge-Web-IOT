-- migrate_to_simple_schema.sql
-- Migration untuk simplify schema - semua data ke monitoring_logs
-- Jalankan di Neon SQL Editor

-- Step 1: Backup data yang penting (optional)
-- CREATE TABLE monitoring_logs_backup AS SELECT * FROM monitoring_logs;

-- Step 2: Drop table water_level_readings (tidak dipakai lagi)
DROP TABLE IF EXISTS water_level_readings CASCADE;

-- Step 3: Modify monitoring_logs - DROP location, ADD level
ALTER TABLE monitoring_logs 
DROP COLUMN IF EXISTS location;

ALTER TABLE monitoring_logs 
ADD COLUMN IF NOT EXISTS level DECIMAL(10,2);

-- Step 4: Rename created_at ke timestamp untuk konsistensi
-- (Optional - kalau mau konsisten namanya)
-- ALTER TABLE monitoring_logs RENAME COLUMN created_at TO timestamp;

-- Step 5: Verify struktur tabel
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;

-- Expected result:
-- id | integer | NO
-- ph_value | numeric | NO
-- battery_level | numeric | NO
-- level | numeric | YES
-- created_at | timestamp without time zone | YES (atau NO tergantung default)
