-- Script SQL untuk membuat tabel monitoring_logs
-- Jalankan di Neon DB Console atau melalui psql

CREATE TABLE IF NOT EXISTS monitoring_logs (
    id SERIAL PRIMARY KEY,
    ph_value DECIMAL(4,2) NOT NULL,
    battery_level DECIMAL(5,2) NOT NULL,
    location VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Index untuk query berdasarkan location dan waktu
CREATE INDEX IF NOT EXISTS idx_monitoring_location ON monitoring_logs(location);
CREATE INDEX IF NOT EXISTS idx_monitoring_created_at ON monitoring_logs(created_at DESC);

-- Contoh query untuk melihat data terbaru
-- SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 10;
