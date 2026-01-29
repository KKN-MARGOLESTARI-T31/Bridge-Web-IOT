-- Script SQL untuk membuat tabel monitoring_logs (MySQL Version)
-- Jalankan di phpMyAdmin atau MySQL Console

-- Database creation is handled by setup script or manually
-- CREATE DATABASE IF NOT EXISTS iot_database;
-- USE iot_database;

CREATE TABLE IF NOT EXISTS monitoring_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ph_value DECIMAL(4,2) NOT NULL,
    battery_level DECIMAL(5,2) NOT NULL,
    location VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk query based on location and time
CREATE INDEX idx_monitoring_location ON monitoring_logs(location);
CREATE INDEX idx_monitoring_created_at ON monitoring_logs(created_at DESC);

-- Contoh query untuk melihat data terbaru
-- SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 10;
