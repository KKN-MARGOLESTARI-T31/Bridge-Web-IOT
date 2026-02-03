-- setup_device_controls.sql
-- Initialize device_controls table with default pump command

-- Create table if not exists (with correct case-sensitive column names)
CREATE TABLE IF NOT EXISTS device_controls (
    id SERIAL PRIMARY KEY,
    command VARCHAR(50) NOT NULL,
    "updatedAt" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX IF NOT EXISTS idx_device_controls_updated ON device_controls("updatedAt" DESC);

-- Insert default command (POMPA_OFF)
INSERT INTO device_controls (command, "updatedAt") 
VALUES ('POMPA_OFF', NOW());

-- Verify
SELECT * FROM device_controls ORDER BY "updatedAt" DESC LIMIT 5;

-- ========================================
-- USAGE EXAMPLES
-- ========================================

-- Turn pump ON:
-- INSERT INTO device_controls (command, "updatedAt") VALUES ('POMPA_ON', NOW());

-- Turn pump OFF:
-- INSERT INTO device_controls (command, "updatedAt") VALUES ('POMPA_OFF', NOW());

-- View history:
-- SELECT * FROM device_controls ORDER BY "updatedAt" DESC LIMIT 10;
