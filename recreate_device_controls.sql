-- recreate_device_controls.sql
-- Simplifies the table as requested: id, command, updated_at
-- No seeding.

DROP TABLE IF EXISTS device_controls;

CREATE TABLE device_controls (
    id SERIAL PRIMARY KEY,
    command VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
