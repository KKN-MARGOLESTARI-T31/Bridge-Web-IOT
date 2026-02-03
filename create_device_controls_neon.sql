-- create_device_controls_neon.sql
-- Create device_controls table in Neon database
-- Based on production schema analysis

-- Create table with exact production schema
CREATE TABLE IF NOT EXISTS device_controls (
    id TEXT PRIMARY KEY,
    "deviceId" TEXT,
    mode TEXT,
    command TEXT NOT NULL DEFAULT 'OFF',
    "updatedAt" TIMESTAMP NOT NULL,
    "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "actionBy" TEXT,
    reason TEXT,
    CONSTRAINT device_controls_deviceId_mode_key UNIQUE ("deviceId", mode)
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS "device_controls_updatedAt_idx" ON device_controls ("updatedAt" DESC);

-- Insert default pump control command
INSERT INTO device_controls (id, "deviceId", mode, command, "updatedAt", "createdAt")
VALUES ('pump_default', 'ALL', 'PUMP', 'OFF', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- Verify table creation
SELECT table_name, column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'device_controls' 
ORDER BY ordinal_position;

-- Verify data insertion
SELECT * FROM device_controls;
