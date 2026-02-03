-- add_pump_status_column.sql
-- Add pump_status column to monitoring_logs table for bi-directional sync

-- Add pump_status column (stores 'true' or 'false' as TEXT)
ALTER TABLE monitoring_logs 
ADD COLUMN IF NOT EXISTS pump_status TEXT DEFAULT 'false';

-- Add comment
COMMENT ON COLUMN monitoring_logs.pump_status IS 'Pump status reported by ESP32 hardware (true/false)';

-- Verify column was added
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;
