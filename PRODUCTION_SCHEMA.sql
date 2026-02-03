-- Production Schema Documentation
-- Based on actual database structure read on 2026-02-03

-- ACTUAL PRODUCTION SCHEMA:
-- monitoring_logs table has these columns:

/*
Column Name      | Data Type           | Nullable | Default           | Notes
-----------------|---------------------|----------|-------------------|---------------------------
id               | TEXT                | NO       |                   | PRIMARY KEY (manual UUID)
battery_level    | DOUBLE PRECISION    | YES      |                   | 
ph_value         | DOUBLE PRECISION    | YES      |                   |
level            | DOUBLE PRECISION    | YES      |                   | Water level in cm
temperature      | DOUBLE PRECISION    | YES      |                   | Temperature in Celsius
signal_strength  | INTEGER             | YES      |                   | Signal strength (NOT 'signal')
created_at       | TIMESTAMP           | NO       | CURRENT_TIMESTAMP | Auto-generated
deviceId         | TEXT                | YES      |                   | Device identifier
*/

-- NO MIGRATION NEEDED - Schema is already complete!

-- Sample INSERT (matching production):
/*
INSERT INTO monitoring_logs (
    id, 
    battery_level, 
    ph_value, 
    level, 
    temperature, 
    signal_strength, 
    created_at, 
    deviceId
) VALUES (
    'iot_unique_id_here',
    85.5,
    7.2,
    25.3,
    28.5,
    18,
    '2026-02-03 00:20:00',
    'ESP32-KKN-01'
);
*/

-- Verify current structure:
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;

-- View recent data:
SELECT * FROM monitoring_logs 
ORDER BY created_at DESC 
LIMIT 5;
