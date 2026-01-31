-- seed_only.sql - HANYA Seeding Data (Tanpa Create Table)
-- Table sudah ada di database, script ini HANYA insert data

-- ==============================================
-- 1. SEED PH READINGS (20 data)
-- ==============================================

INSERT INTO ph_readings (id, value, location, timestamp, "deviceId", temperature) VALUES
-- Data hari ini - Kolam
('ph_' || extract(epoch from now())::bigint || '_001', 7.2, 'kolam', NOW() - INTERVAL '1 hour', 'ESP32_001', 28.5),
('ph_' || extract(epoch from now())::bigint || '_002', 7.4, 'kolam', NOW() - INTERVAL '2 hours', 'ESP32_001', 28.3),
('ph_' || extract(epoch from now())::bigint || '_003', 7.1, 'kolam', NOW() - INTERVAL '3 hours', 'ESP32_001', 28.7),
('ph_' || extract(epoch from now())::bigint || '_004', 7.3, 'kolam', NOW() - INTERVAL '4 hours', 'ESP32_001', 28.4),
('ph_' || extract(epoch from now())::bigint || '_005', 7.0, 'kolam', NOW() - INTERVAL '5 hours', 'ESP32_001', 28.6),

-- Data hari ini - Sawah
('ph_' || extract(epoch from now())::bigint || '_006', 6.8, 'sawah', NOW() - INTERVAL '1 hour', 'ESP32_002', 27.2),
('ph_' || extract(epoch from now())::bigint || '_007', 6.9, 'sawah', NOW() - INTERVAL '2 hours', 'ESP32_002', 27.5),
('ph_' || extract(epoch from now())::bigint || '_008', 6.7, 'sawah', NOW() - INTERVAL '3 hours', 'ESP32_002', 27.1),
('ph_' || extract(epoch from now())::bigint || '_009', 7.0, 'sawah', NOW() - INTERVAL '4 hours', 'ESP32_002', 27.3),
('ph_' || extract(epoch from now())::bigint || '_010', 6.8, 'sawah', NOW() - INTERVAL '5 hours', 'ESP32_002', 27.4),

-- Data kemarin - Kolam
('ph_' || extract(epoch from now())::bigint || '_011', 7.5, 'kolam', NOW() - INTERVAL '1 day' - INTERVAL '2 hours', 'ESP32_001', 28.8),
('ph_' || extract(epoch from now())::bigint || '_012', 7.3, 'kolam', NOW() - INTERVAL '1 day' - INTERVAL '4 hours', 'ESP32_001', 28.6),
('ph_' || extract(epoch from now())::bigint || '_013', 7.2, 'kolam', NOW() - INTERVAL '1 day' - INTERVAL '6 hours', 'ESP32_001', 28.5),

-- Data kemarin - Sawah
('ph_' || extract(epoch from now())::bigint || '_014', 6.6, 'sawah', NOW() - INTERVAL '1 day' - INTERVAL '2 hours', 'ESP32_002', 27.0),
('ph_' || extract(epoch from now())::bigint || '_015', 6.7, 'sawah', NOW() - INTERVAL '1 day' - INTERVAL '4 hours', 'ESP32_002', 27.2),

-- Data 2-7 hari lalu
('ph_' || extract(epoch from now())::bigint || '_016', 7.1, 'kolam', NOW() - INTERVAL '2 days', 'ESP32_001', 28.4),
('ph_' || extract(epoch from now())::bigint || '_017', 6.9, 'sawah', NOW() - INTERVAL '3 days', 'ESP32_002', 27.3),
('ph_' || extract(epoch from now())::bigint || '_018', 7.4, 'kolam', NOW() - INTERVAL '5 days', 'ESP32_001', 28.7),
('ph_' || extract(epoch from now())::bigint || '_019', 6.8, 'sawah', NOW() - INTERVAL '6 days', 'ESP32_002', 27.1),
('ph_' || extract(epoch from now())::bigint || '_020', 7.2, 'kolam', NOW() - INTERVAL '7 days', 'ESP32_001', 28.5);

-- ==============================================
-- 2. SEED WATER LEVEL READINGS (20 data)
-- ==============================================

INSERT INTO water_level_readings (id, level, location, timestamp, status) VALUES
-- Data hari ini - Kolam
('wl_' || extract(epoch from now())::bigint || '_001', 85.5, 'kolam', NOW() - INTERVAL '1 hour', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_002', 87.2, 'kolam', NOW() - INTERVAL '2 hours', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_003', 82.1, 'kolam', NOW() - INTERVAL '3 hours', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_004', 88.5, 'kolam', NOW() - INTERVAL '4 hours', 'high'),
('wl_' || extract(epoch from now())::bigint || '_005', 84.0, 'kolam', NOW() - INTERVAL '5 hours', 'normal'),

-- Data hari ini - Sawah
('wl_' || extract(epoch from now())::bigint || '_006', 45.5, 'sawah', NOW() - INTERVAL '1 hour', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_007', 47.2, 'sawah', NOW() - INTERVAL '2 hours', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_008', 42.8, 'sawah', NOW() - INTERVAL '3 hours', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_009', 38.5, 'sawah', NOW() - INTERVAL '4 hours', 'low'),
('wl_' || extract(epoch from now())::bigint || '_010', 44.0, 'sawah', NOW() - INTERVAL '5 hours', 'normal'),

-- Data kemarin - Kolam
('wl_' || extract(epoch from now())::bigint || '_011', 90.5, 'kolam', NOW() - INTERVAL '1 day' - INTERVAL '2 hours', 'high'),
('wl_' || extract(epoch from now())::bigint || '_012', 86.3, 'kolam', NOW() - INTERVAL '1 day' - INTERVAL '4 hours', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_013', 83.2, 'kolam', NOW() - INTERVAL '1 day' - INTERVAL '6 hours', 'normal'),

-- Data kemarin - Sawah
('wl_' || extract(epoch from now())::bigint || '_014', 50.6, 'sawah', NOW() - INTERVAL '1 day' - INTERVAL '2 hours', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_015', 48.7, 'sawah', NOW() - INTERVAL '1 day' - INTERVAL '4 hours', 'normal'),

-- Data 2-7 hari lalu
('wl_' || extract(epoch from now())::bigint || '_016', 81.0, 'kolam', NOW() - INTERVAL '2 days', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_017', 46.5, 'sawah', NOW() - INTERVAL '3 days', 'normal'),
('wl_' || extract(epoch from now())::bigint || '_018', 92.5, 'kolam', NOW() - INTERVAL '5 days', 'high'),
('wl_' || extract(epoch from now())::bigint || '_019', 35.0, 'sawah', NOW() - INTERVAL '6 days', 'low'),
('wl_' || extract(epoch from now())::bigint || '_020', 84.5, 'kolam', NOW() - INTERVAL '7 days', 'normal');

-- ==============================================
-- 3. UPDATE DEVICE STATUS (jika sudah ada)
-- ==============================================

UPDATE device_status SET
    "activeMode" = 'kolam',
    battery = 87.5,
    signal = -45,
    "lastUpdate" = CURRENT_TIMESTAMP
WHERE id = 'global-device';

-- Jika belum ada, insert
INSERT INTO device_status (id, "activeMode", battery, signal, "lastUpdate")
SELECT 'global-device', 'kolam', 87.5, -45, CURRENT_TIMESTAMP
WHERE NOT EXISTS (SELECT 1 FROM device_status WHERE id = 'global-device');

-- ==============================================
-- VERIFY (Optional - bisa di-comment jika tidak perlu)
-- ==============================================

SELECT 'ph_readings' as table_name, COUNT(*) as row_count FROM ph_readings
UNION ALL
SELECT 'water_level_readings', COUNT(*) FROM water_level_readings
UNION ALL
SELECT 'device_status', COUNT(*) FROM device_status
ORDER BY table_name;
