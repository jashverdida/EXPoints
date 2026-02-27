-- =============================================================================
-- php_sessions table for Supabase (PostgreSQL)
-- Run this ONCE in: Supabase Dashboard → SQL Editor
-- =============================================================================

CREATE TABLE IF NOT EXISTS php_sessions (
    session_id   VARCHAR(128)             NOT NULL,
    session_data TEXT                     NOT NULL DEFAULT '',
    expires_at   TIMESTAMP WITH TIME ZONE NOT NULL,
    PRIMARY KEY  (session_id)
);

CREATE INDEX IF NOT EXISTS idx_php_sessions_expires
    ON php_sessions (expires_at);

-- Service-role key bypasses RLS automatically, but disable it to be safe:
ALTER TABLE php_sessions DISABLE ROW LEVEL SECURITY;
