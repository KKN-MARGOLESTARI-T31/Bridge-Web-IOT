-- create_admin_messages.sql
-- Create table for AdminMessage model (Prisma translation)
-- Reference: User request

CREATE TABLE IF NOT EXISTS admin_messages (
    id TEXT PRIMARY KEY,
    "userId" TEXT NOT NULL,
    message TEXT NOT NULL,
    "isRead" BOOLEAN NOT NULL DEFAULT FALSE,
    "readAt" TIMESTAMP,
    "createdAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraint: Foreign Key to users table
    -- Ensure 'users' table exists with primary key 'id'
    CONSTRAINT fk_admin_messages_user FOREIGN KEY ("userId") REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS "idx_admin_messages_userId_createdAt" ON admin_messages("userId", "createdAt");
CREATE INDEX IF NOT EXISTS "idx_admin_messages_isRead" ON admin_messages("isRead");

-- Verification
SELECT table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'admin_messages';
