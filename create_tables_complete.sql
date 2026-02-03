-- ========================================
-- CREATE TABLE: monitoring_logs
-- Untuk Neon SQL Editor
-- Schema: Simplified - All IoT data in one table
-- ========================================

-- Step 1: Drop existing table (HATI-HATI: ini akan hapus semua data!)
-- Uncomment baris dibawah jika ingin hapus table lama dan mulai dari 0
-- DROP TABLE IF EXISTS monitoring_logs CASCADE;

-- Step 2: Create monitoring_logs table
CREATE TABLE IF NOT EXISTS monitoring_logs (
    id SERIAL PRIMARY KEY,
    ph_value DECIMAL(4,2) NOT NULL,
    battery_level DECIMAL(5,2) NOT NULL,
    level DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Step 3: Create indexes untuk performance
CREATE INDEX IF NOT EXISTS idx_monitoring_created_at ON monitoring_logs(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_monitoring_ph ON monitoring_logs(ph_value);
CREATE INDEX IF NOT EXISTS idx_monitoring_level ON monitoring_logs(level);

-- Step 4: Verify table structure
SELECT 
    column_name, 
    data_type, 
    is_nullable, 
    column_default
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;

-- Expected output:
-- column_name    | data_type | is_nullable | column_default
-- ---------------+-----------+-------------+-------------------------------
-- id             | integer   | NO          | nextval('monitoring_logs_id_seq'::regclass)
-- ph_value       | numeric   | NO          | NULL
-- battery_level  | numeric   | NO          | NULL
-- level          | numeric   | YES         | NULL
-- created_at     | timestamp | YES         | CURRENT_TIMESTAMP

-- ========================================
-- OPTIONAL: Insert sample data untuk testing
-- ========================================

-- Uncomment untuk insert sample data
/*
INSERT INTO monitoring_logs (ph_value, battery_level, level, created_at) VALUES
(7.0, 85.5, 15.3, '2026-02-01 10:00:00'),
(6.8, 84.2, 14.8, '2026-02-01 10:05:00'),
(7.2, 83.8, 16.1, '2026-02-01 10:10:00'),
(6.9, 83.5, 15.5, '2026-02-01 10:15:00'),
(7.1, 82.9, 14.9, '2026-02-01 10:20:00');
*/

-- ========================================
-- CREATE TABLE: device_controls (untuk relay pompa)
-- ========================================

CREATE TABLE IF NOT EXISTS device_controls (
    id SERIAL PRIMARY KEY,
    command VARCHAR(50) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index
CREATE INDEX IF NOT EXISTS idx_device_controls_updated ON device_controls(updated_at DESC);

-- Insert default command
INSERT INTO device_controls (command, updated_at) 
VALUES ('POMPA_OFF', NOW())
ON CONFLICT DO NOTHING;

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Check total records
SELECT COUNT(*) as total_records FROM monitoring_logs;

-- Check latest 5 records
SELECT id, ph_value, battery_level, level, created_at 
FROM monitoring_logs 
ORDER BY created_at DESC 
LIMIT 5;

-- Check device controls
SELECT * FROM device_controls ORDER BY updated_at DESC LIMIT 1;
