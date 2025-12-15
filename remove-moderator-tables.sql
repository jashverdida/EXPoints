-- ============================================
-- OPTIONAL: Remove Moderator Tables (If They Exist)
-- Run this ONLY if you have moderator tables and want to remove them
-- ============================================

-- Drop moderation tables if they exist
DROP TABLE IF EXISTS moderation_reports CASCADE;
DROP TABLE IF EXISTS moderators CASCADE;

-- Success message
DO $$
BEGIN
    RAISE NOTICE '✅ Moderator tables removed successfully!';
END $$;
