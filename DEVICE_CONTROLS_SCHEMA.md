# Production device_controls Schema Documentation

## Actual Schema (from production database)

```sql
CREATE TABLE device_controls (
    id TEXT PRIMARY KEY,
    "deviceId" TEXT,
    mode TEXT,
    command TEXT NOT NULL DEFAULT 'OFF',
    "updatedAt" TIMESTAMP NOT NULL,
    "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "actionBy" TEXT,
    reason TEXT,
    UNIQUE ("deviceId", mode)
);
```

## Column Details

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | TEXT | NO | - | Primary key (manual ID) |
| deviceId | TEXT | YES | - | Device identifier (e.g., 'ESP32-KKN-01', 'ALL') |
| mode | TEXT | YES | - | Control mode (e.g., 'PUMP', 'VALVE') |
| command | TEXT | NO | 'OFF' | Command value ('ON', 'OFF', etc) |
| updatedAt | TIMESTAMP | NO | - | Last update timestamp |
| createdAt | TIMESTAMP | NO | CURRENT_TIMESTAMP | Creation timestamp |
| actionBy | TEXT | YES | - | Who/what triggered the command |
| reason | TEXT | YES | - | Why the command was issued |

## Constraints

- **PRIMARY KEY**: `id`
- **UNIQUE**: `(deviceId, mode)` - One device can only have one command per mode

## Indexes

- `device_controls_pkey` - Primary key on `id`
- `device_controls_updatedAt_idx` - Index on `updatedAt` DESC
- `device_controls_deviceId_mode_key` - Unique index on `(deviceId, mode)`

## Usage Examples

### Insert/Update Pump Command (UPSERT)

```sql
-- For specific device
INSERT INTO device_controls (id, "deviceId", mode, command, "updatedAt")
VALUES ('pump_esp32_001', 'ESP32-KKN-01', 'PUMP', 'ON', NOW())
ON CONFLICT ("deviceId", mode) 
DO UPDATE SET command = EXCLUDED.command, "updatedAt" = EXCLUDED."updatedAt";

-- For all devices (global)
INSERT INTO device_controls (id, "deviceId", mode, command, "updatedAt")
VALUES ('pump_global', 'ALL', 'PUMP', 'OFF', NOW())
ON CONFLICT ("deviceId", mode) 
DO UPDATE SET command = EXCLUDED.command, "updatedAt" = EXCLUDED."updatedAt";
```

### Query Latest Command

```sql
-- For specific device or global
SELECT command FROM device_controls 
WHERE ("deviceId" = 'ESP32-KKN-01' OR "deviceId" = 'ALL') 
AND mode = 'PUMP' 
ORDER BY "updatedAt" DESC 
LIMIT 1;
```

### Update Command

```sql
UPDATE device_controls 
SET command = 'ON', "updatedAt" = NOW(), "actionBy" = 'Admin', reason = 'Manual control'
WHERE "deviceId" = 'ALL' AND mode = 'PUMP';
```

## Command Values

For PUMP mode:
- `ON` or `POMPA_ON` - Turn pump ON
- `OFF` or `POMPA_OFF` - Turn pump OFF

## Integration with input.php

When ESP32 sends data, `input.php` queries:
1. Check for device-specific command (`deviceId` = ESP32's ID)
2. Fallback to global command (`deviceId` = 'ALL')
3. Filter by `mode = 'PUMP'`
4. Return latest command based on `updatedAt`

Response mapping:
- `ON` → send `POMPA_ON` to ESP32
- `OFF` → send `POMPA_OFF` to ESP32
