-- =============================================================================
-- php_sessions table
-- Required for database-backed PHP sessions on Vercel serverless deployments.
-- Run this migration once against your cloud MySQL database before deploying.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `php_sessions` (
    `session_id`   VARCHAR(128)    NOT NULL,
    `session_data` MEDIUMTEXT      NOT NULL DEFAULT '',
    `expires_at`   DATETIME        NOT NULL,
    PRIMARY KEY (`session_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
