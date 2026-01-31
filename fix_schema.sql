-- fix_schema.sql - Drop old tables and create correct schema
-- Run this first to fix the schema mismatch

-- ==============================================
-- 1. DROP OLD TABLES (if exist)
-- ==============================================
DROP TABLE IF EXISTS ph_readings CASCADE;
DROP TABLE IF EXISTS water_level_readings CASCADE;
DROP TABLE IF EXISTS device_status CASCADE;

-- ==============================================
-- 2. CREATE DEVICE_STATUS TABLE (Correct Schema)
-- ==============================================
CREATE TABLE device_status (
    id VARCHAR(50) PRIMARY KEY DEFAULT 'global-device',
    "activeMode" VARCHAR(20) CHECK ("activeMode" IN ('sawah', 'kolam')),
    battery DECIMAL(5,2),
    signal INTEGER,
    "lastUpdate" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default device
INSERT INTO device_status (id, "activeMode", battery, signal, "lastUpdate")
VALUES ('global-device', 'sawah', 85.5, -45, CURRENT_TIMESTAMP);

-- ==============================================
-- 3. CREATE PH_READINGS TABLE (Correct Schema)
-- ==============================================
CREATE TABLE ph_readings (
    id VARCHAR(50) PRIMARY KEY,
    value DECIMAL(4,2) NOT NULL,
    location VARCHAR(20) CHECK (location IN ('kolam', 'sawah')),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "deviceId" VARCHAR(50),
    temperature DECIMAL(5,2)
);

-- Index for performance
CREATE INDEX idx_ph_timestamp ON ph_readings(timestamp DESC);
CREATE INDEX idx_ph_location ON ph_readings(location);

-- ==============================================
-- 4. CREATE WATER_LEVEL_READINGS TABLE (Correct Schema)
-- ==============================================
CREATE TABLE water_level_readings (
    id VARCHAR(50) PRIMARY KEY,
    level DECIMAL(8,2) NOT NULL,
    location VARCHAR(20) CHECK (location IN ('kolam', 'sawah')),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'normal' CHECK (status IN ('low', 'normal', 'high', 'critical'))
);

-- Index for performance
CREATE INDEX idx_water_timestamp ON water_level_readings(timestamp DESC);
CREATE INDEX idx_water_location ON water_level_readings(location);

-- ==============================================
-- 5. VERIFY TABLES CREATED
-- ==============================================
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public' 
ORDER BY table_name;
