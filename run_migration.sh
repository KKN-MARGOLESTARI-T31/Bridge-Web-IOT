#!/bin/bash
# run_migration.sh - Quick script to add pump_status column

echo "🔧 Adding pump_status column to monitoring_logs table..."

# Load database URL from .env
if [ -f .env ]; then
    export $(cat .env | grep DATABASE_URL | xargs)
fi

# Run migration
psql "$DATABASE_URL" -f add_pump_status_column.sql

if [ $? -eq 0 ]; then
    echo "✅ Migration successful!"
    echo ""
    echo "You can now restart your ESP32 to test the sync."
else
    echo "❌ Migration failed. Check the error message above."
    exit 1
fi
