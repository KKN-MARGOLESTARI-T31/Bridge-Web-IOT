-- seed_manual.sql - Complete Seeding untuk Neon PostgreSQL
-- Jalankan di Neon Console > SQL Editor
-- Updated: 2026-01-30 - Include device_status table

-- ==============================================
-- 0. CREATE DEVICE_STATUS TABLE (if not exists)
-- ==============================================
CREATE TABLE IF NOT EXISTS device_status (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(50) NOT NULL,
    mode VARCHAR(20) DEFAULT 'active',
    battery_level DECIMAL(5,2),
    signal_strength INTEGER,
    firmware_version VARCHAR(20),
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_device_status_device_id ON device_status(device_id);
CREATE INDEX IF NOT EXISTS idx_device_status_last_seen ON device_status(last_seen DESC);

-- ==============================================
-- 1. Verify All Tables Exist
-- ==============================================
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public'
ORDER BY table_name;

-- ==============================================
-- 2. Insert Device Status Data (5 devices)
-- ==============================================
INSERT INTO device_status (device_id, mode, battery_level, signal_strength, firmware_version, last_seen) VALUES
('ESP32_001', 'active', 87.5, -45, 'v1.2.3', NOW() - INTERVAL '5 minutes'),
('ESP32_002', 'active', 92.0, -52, 'v1.2.3', NOW() - INTERVAL '2 minutes'),
('ESP32_003', 'sleep', 45.3, -68, 'v1.2.2', NOW() - INTERVAL '1 hour'),
('IoT_Device_A', 'active', 78.8, -38, 'v1.3.0', NOW() - INTERVAL '10 minutes'),
('IoT_Device_B', 'maintenance', 100.0, -42, 'v1.3.0', NOW() - INTERVAL '30 minutes');

-- ==============================================
-- 3. Insert Sample pH Readings (20 data)
-- ==============================================
INSERT INTO ph_readings (ph_value, device_id, notes, timestamp) VALUES
-- Hari ini
(7.2, 'ESP32_001', 'pH normal - sawah bagian utara', NOW() - INTERVAL '1 hour'),
(6.8, 'ESP32_002', 'pH sedikit asam - kolam ikan', NOW() - INTERVAL '2 hours'),
(7.5, 'ESP32_003', 'pH optimal - sumur', NOW() - INTERVAL '3 hours'),
(6.5, 'IoT_Device_A', 'pH rendah - sungai', NOW() - INTERVAL '4 hours'),
(7.0, 'IoT_Device_B', 'pH normal - tandon air', NOW() - INTERVAL '5 hours'),

-- Kemarin
(7.3, 'ESP32_001', 'pH stabil - sawah', NOW() - INTERVAL '1 day' - INTERVAL '2 hours'),
(6.9, 'ESP32_002', 'pH normal - kolam', NOW() - INTERVAL '1 day' - INTERVAL '4 hours'),
(7.1, 'ESP32_003', 'pH baik - sumur', NOW() - INTERVAL '1 day' - INTERVAL '6 hours'),
(6.7, 'IoT_Device_A', 'pH menurun - sungai', NOW() - INTERVAL '1 day' - INTERVAL '8 hours'),
(7.4, 'IoT_Device_B', 'pH tinggi - tandon', NOW() - INTERVAL '1 day' - INTERVAL '10 hours'),

-- 2 hari lalu
(7.0, 'ESP32_001', 'pH normal - sawah', NOW() - INTERVAL '2 days' - INTERVAL '3 hours'),
(6.6, 'ESP32_002', 'pH rendah - kolam', NOW() - INTERVAL '2 days' - INTERVAL '5 hours'),
(7.6, 'ESP32_003', 'pH tinggi - sumur', NOW() - INTERVAL '2 days' - INTERVAL '7 hours'),
(6.4, 'IoT_Device_A', 'pH sangat rendah - sungai', NOW() - INTERVAL '2 days' - INTERVAL '9 hours'),
(7.2, 'IoT_Device_B', 'pH normal - tandon', NOW() - INTERVAL '2 days' - INTERVAL '11 hours'),

-- 3-7 hari lalu (data lama)
(6.9, 'ESP32_001', 'Data historis', NOW() - INTERVAL '3 days'),
(7.1, 'ESP32_002', 'Data historis', NOW() - INTERVAL '4 days'),
(7.3, 'ESP32_003', 'Data historis', NOW() - INTERVAL '5 days'),
(6.8, 'IoT_Device_A', 'Data historis', NOW() - INTERVAL '6 days'),
(7.5, 'IoT_Device_B', 'Data historis', NOW() - INTERVAL '7 days');

-- ==============================================
-- 4. Insert Sample Water Level Readings (20 data)
-- ==============================================
INSERT INTO water_level_readings (water_level, device_id, notes, timestamp) VALUES
-- Hari ini
(45, 'ESP32_001', 'Level normal - sawah', NOW() - INTERVAL '1 hour'),
(78, 'ESP32_002', 'Level tinggi - kolam', NOW() - INTERVAL '2 hours'),
(32, 'ESP32_003', 'Level rendah - sumur', NOW() - INTERVAL '3 hours'),
(91, 'IoT_Device_A', 'Level maksimal - sungai', NOW() - INTERVAL '4 hours'),
(56, 'IoT_Device_B', 'Level sedang - tandon', NOW() - INTERVAL '5 hours'),

-- Kemarin
(48, 'ESP32_001', 'Level naik - sawah', NOW() - INTERVAL '1 day' - INTERVAL '2 hours'),
(82, 'ESP32_002', 'Level tinggi - kolam', NOW() - INTERVAL '1 day' - INTERVAL '4 hours'),
(28, 'ESP32_003', 'Level turun - sumur', NOW() - INTERVAL '1 day' - INTERVAL '6 hours'),
(95, 'IoT_Device_A', 'Level overflow - sungai', NOW() - INTERVAL '1 day' - INTERVAL '8 hours'),
(60, 'IoT_Device_B', 'Level naik - tandon', NOW() - INTERVAL '1 day' - INTERVAL '10 hours'),

-- 2 hari lalu
(42, 'ESP32_001', 'Level turun - sawah', NOW() - INTERVAL '2 days' - INTERVAL '3 hours'),
(75, 'ESP32_002', 'Level normal - kolam', NOW() - INTERVAL '2 days' - INTERVAL '5 hours'),
(35, 'ESP32_003', 'Level naik sedikit - sumur', NOW() - INTERVAL '2 days' - INTERVAL '7 hours'),
(88, 'IoT_Device_A', 'Level tinggi - sungai', NOW() - INTERVAL '2 days' - INTERVAL '9 hours'),
(52, 'IoT_Device_B', 'Level normal - tandon', NOW() - INTERVAL '2 days' - INTERVAL '11 hours'),

-- 3-7 hari lalu (data lama)
(50, 'ESP32_001', 'Data historis', NOW() - INTERVAL '3 days'),
(80, 'ESP32_002', 'Data historis', NOW() - INTERVAL '4 days'),
(30, 'ESP32_003', 'Data historis', NOW() - INTERVAL '5 days'),
(92, 'IoT_Device_A', 'Data historis', NOW() - INTERVAL '6 days'),
(58, 'IoT_Device_B', 'Data historis', NOW() - INTERVAL '7 days');

-- ==============================================
-- 5. Verify Data Inserted
-- ==============================================

-- Count total rows per table
SELECT 'device_status' as table_name, COUNT(*) as row_count FROM device_status
UNION ALL
SELECT 'ph_readings', COUNT(*) FROM ph_readings
UNION ALL
SELECT 'water_level_readings', COUNT(*) FROM water_level_readings
ORDER BY table_name;

-- ==============================================
-- 6. View Latest Data from Each Table
-- ==============================================

-- Latest 5 device status
SELECT 
    device_id,
    mode,
    battery_level,
    signal_strength,
    firmware_version,
    last_seen
FROM device_status 
ORDER BY last_seen DESC 
LIMIT 5;

-- Latest 5 pH readings
SELECT 
    id,
    ph_value,
    device_id,
    notes,
    timestamp
FROM ph_readings 
ORDER BY timestamp DESC 
LIMIT 5;

-- Latest 5 water level readings
SELECT 
    id,
    water_level,
    device_id,
    notes,
    timestamp
FROM water_level_readings 
ORDER BY timestamp DESC 
LIMIT 5;

-- ==============================================
-- 7. Aggregation Queries (Analytics)
-- ==============================================

-- Device status summary
SELECT 
    mode,
    COUNT(*) as device_count,
    ROUND(AVG(battery_level)::numeric, 2) as avg_battery,
    ROUND(AVG(signal_strength)::numeric, 2) as avg_signal
FROM device_status
GROUP BY mode
ORDER BY device_count DESC;

-- Average pH by device (last 7 days)
SELECT 
    device_id, 
    ROUND(AVG(ph_value)::numeric, 2) as avg_ph,
    MIN(ph_value) as min_ph,
    MAX(ph_value) as max_ph,
    COUNT(*) as total_readings
FROM ph_readings 
WHERE timestamp > NOW() - INTERVAL '7 days'
GROUP BY device_id
ORDER BY avg_ph DESC;

-- Average water level by device (last 7 days)
SELECT 
    device_id,
    ROUND(AVG(water_level)::numeric, 2) as avg_level,
    MIN(water_level) as min_level,
    MAX(water_level) as max_level,
    COUNT(*) as total_readings
FROM water_level_readings
WHERE timestamp > NOW() - INTERVAL '7 days'
GROUP BY device_id
ORDER BY avg_level DESC;

-- Combined view: Latest readings with device status
SELECT 
    ds.device_id,
    ds.mode,
    ds.battery_level,
    ds.signal_strength,
    ph.ph_value as latest_ph,
    wl.water_level as latest_water_level,
    GREATEST(ds.last_seen, ph.timestamp, wl.timestamp) as last_activity
FROM device_status ds
LEFT JOIN LATERAL (
    SELECT ph_value, timestamp 
    FROM ph_readings 
    WHERE device_id = ds.device_id 
    ORDER BY timestamp DESC 
    LIMIT 1
) ph ON true
LEFT JOIN LATERAL (
    SELECT water_level, timestamp 
    FROM water_level_readings 
    WHERE device_id = ds.device_id 
    ORDER BY timestamp DESC 
    LIMIT 1
) wl ON true
ORDER BY last_activity DESC;

-- ==============================================
-- ✅ Seeding Completed!
-- ==============================================
-- Total data inserted:
--   - device_status: 5 devices
--   - ph_readings: 20 records
--   - water_level_readings: 20 records
-- ==============================================

