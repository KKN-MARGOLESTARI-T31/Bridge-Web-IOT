-- SQL Script to seed monitoring_logs with dummy data
-- Run this in Neon Console if you cannot run PHP scripts locally

INSERT INTO monitoring_logs (ph_value, battery_level, location, created_at) VALUES 
(7.2, 85.5, 'sawah', NOW() - INTERVAL '1 hour'),
(6.8, 82.0, 'kolam', NOW() - INTERVAL '2 hours'),
(7.5, 90.0, 'sumur', NOW() - INTERVAL '3 hours'),
(7.1, 84.5, 'sawah', NOW() - INTERVAL '4 hours'),
(6.9, 81.0, 'kolam', NOW() - INTERVAL '5 hours'),
(7.3, 88.0, 'sumur', NOW() - INTERVAL '6 hours'),
(7.0, 83.5, 'sawah', NOW() - INTERVAL '1 day'),
(6.7, 80.0, 'kolam', NOW() - INTERVAL '1 day 2 hours'),
(7.4, 89.0, 'sumur', NOW() - INTERVAL '1 day 4 hours'),
(7.2, 85.0, 'sawah', NOW() - INTERVAL '2 days');

-- Verify data
SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 10;
