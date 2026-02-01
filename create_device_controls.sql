-- create_device_controls.sql

CREATE TABLE IF NOT EXISTS device_controls (
    id SERIAL PRIMARY KEY,
    location VARCHAR(50) NOT NULL,
    command VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed initial data
INSERT INTO device_controls (location, command) VALUES 
('kolam', 'POMPA_ON'),
('sawah', 'POMPA_OFF')
ON CONFLICT DO NOTHING;
