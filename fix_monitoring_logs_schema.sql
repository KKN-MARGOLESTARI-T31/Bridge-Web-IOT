-- fix_monitoring_logs_schema.sql
-- Add missing columns: signal, pump_status, temperature

-- ========================================
-- STEP 1: Add Missing Columns
-- ========================================

-- Add signal column (integer for signal strength)
ALTER TABLE monitoring_logs 
ADD COLUMN IF NOT EXISTS signal INTEGER;

-- Add pump_status column (boolean for pump on/off)
ALTER TABLE monitoring_logs 
ADD COLUMN IF NOT EXISTS pump_status BOOLEAN;

-- Add temperature column (decimal for temperature in Celsius)
ALTER TABLE monitoring_logs 
ADD COLUMN IF NOT EXISTS temperature DECIMAL(5,2);

-- ========================================
-- STEP 2: Fix ID to be Auto-Increment (SERIAL)
-- ========================================

-- Create sequence if not exists
CREATE SEQUENCE IF NOT EXISTS monitoring_logs_id_seq;

-- Set default value for id column
ALTER TABLE monitoring_logs 
ALTER COLUMN id SET DEFAULT nextval('monitoring_logs_id_seq');

-- Update sequence to start from max id + 1
SELECT setval('monitoring_logs_id_seq', COALESCE((SELECT MAX(CAST(id AS INTEGER)) FROM monitoring_logs WHERE id ~ '^[0-9]+$'), 0) + 1, false);

-- ========================================
-- STEP 3: Verify Schema
-- ========================================

SELECT 
    column_name, 
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;

-- Expected columns:
-- id (integer with default nextval)
-- ph_value (numeric)
-- battery_level (numeric)
-- level (numeric)
-- created_at (timestamp)
-- signal (integer)
-- pump_status (boolean)
-- temperature (numeric)

-- ========================================
-- STEP 4: Create Indexes for New Columns
-- ========================================

CREATE INDEX IF NOT EXISTS idx_monitoring_signal ON monitoring_logs(signal);
CREATE INDEX IF NOT EXISTS idx_monitoring_pump_status ON monitoring_logs(pump_status);
CREATE INDEX IF NOT EXISTS idx_monitoring_temperature ON monitoring_logs(temperature);

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Test insert with all columns
/*
INSERT INTO monitoring_logs (ph_value, battery_level, level, signal, pump_status, temperature, created_at)
VALUES (7.0, 85.5, 15.3, 31, false, 28.5, NOW());
*/

-- Check recent data
SELECT id, ph_value, battery_level, level, signal, pump_status, temperature, created_at
FROM monitoring_logs 
ORDER BY created_at DESC 
LIMIT 5;
