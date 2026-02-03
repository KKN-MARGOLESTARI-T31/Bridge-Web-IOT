-- create_users_minimal.sql
-- Minimal Users table to satisfy Foreign Key constraints
-- Based on typical Prisma User model

CREATE TABLE IF NOT EXISTS users (
    id TEXT PRIMARY KEY,
    email TEXT UNIQUE,
    name TEXT,
    password TEXT,
    "createdAt" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updatedAt" TIMESTAMP
);

-- Index
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
