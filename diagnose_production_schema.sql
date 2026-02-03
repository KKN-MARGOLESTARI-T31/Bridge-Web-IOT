-- diagnose_production_schema.sql
-- Run this in Neon SQL Editor to see actual table structure

-- Check monitoring_logs structure
SELECT 
    column_name, 
    data_type, 
    is_nullable,
    column_default,
    ordinal_position
FROM information_schema.columns 
WHERE table_name = 'monitoring_logs' 
ORDER BY ordinal_position;

-- Check constraints
SELECT
    tc.constraint_name, 
    tc.constraint_type, 
    kcu.column_name
FROM information_schema.table_constraints tc
JOIN information_schema.key_column_usage kcu 
    ON tc.constraint_name = kcu.constraint_name
WHERE tc.table_name = 'monitoring_logs';

-- Sample data to see format
SELECT * FROM monitoring_logs ORDER BY created_at DESC LIMIT 3;
