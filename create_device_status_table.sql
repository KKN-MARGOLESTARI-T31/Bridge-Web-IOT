-- create_device_status_table.sql
-- Membuat tabel device_status untuk tracking status perangkat IoT

CREATE TABLE IF NOT EXISTS device_status (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(50) NOT NULL,
    mode VARCHAR(20) DEFAULT 'active',  -- active, sleep, maintenance, offline
    battery_level DECIMAL(5,2),         -- Persentase baterai (0.00 - 100.00)
    signal_strength INTEGER,            -- Kekuatan sinyal (-100 to 0 dBm)
    firmware_version VARCHAR(20),
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk performa query
CREATE INDEX IF NOT EXISTS idx_device_status_device_id ON device_status(device_id);
CREATE INDEX IF NOT EXISTS idx_device_status_last_seen ON device_status(last_seen DESC);
CREATE INDEX IF NOT EXISTS idx_device_status_mode ON device_status(mode);

-- Comments untuk dokumentasi
COMMENT ON TABLE device_status IS 'Status dan informasi perangkat IoT';
COMMENT ON COLUMN device_status.mode IS 'Mode operasi: active, sleep, maintenance, offline';
COMMENT ON COLUMN device_status.battery_level IS 'Level baterai dalam persentase (0-100)';
COMMENT ON COLUMN device_status.signal_strength IS 'Kekuatan sinyal WiFi/4G dalam dBm (-100 to 0)';
