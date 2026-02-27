<?php
/**
 * Database-backed PHP Session Handler
 *
 * On Vercel serverless, each request may land on a fresh container and the
 * /tmp filesystem is not shared across instances.  Storing sessions in MySQL
 * (the same DB the rest of the app uses) gives us persistent, cross-instance
 * session storage with zero extra infrastructure.
 *
 * Requires the `php_sessions` table – run setup-sessions-table.sql once.
 */

require_once __DIR__ . '/db.php';

class DatabaseSessionHandler implements SessionHandlerInterface
{
    private ?mysqli $db;

    public function __construct()
    {
        $this->db = getDBConnection();
    }

    public function open(string $path, string $name): bool
    {
        return $this->db !== null;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        if (!$this->db) {
            return '';
        }

        $stmt = $this->db->prepare(
            'SELECT session_data FROM php_sessions WHERE session_id = ? AND expires_at > NOW()'
        );
        if (!$stmt) {
            return '';
        }

        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();
        $stmt->close();

        return $row ? (string) $row['session_data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        if (!$this->db) {
            return false;
        }

        $lifetime = (int) ini_get('session.gc_maxlifetime');
        $stmt     = $this->db->prepare(
            'REPLACE INTO php_sessions (session_id, session_data, expires_at)
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ssi', $id, $data, $lifetime);
        return (bool) $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        if (!$this->db) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM php_sessions WHERE session_id = ?');
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('s', $id);
        return (bool) $stmt->execute();
    }

    public function gc(int $max_lifetime): int
    {
        if (!$this->db) {
            return 0;
        }

        $this->db->query('DELETE FROM php_sessions WHERE expires_at < NOW()');
        return max(0, $this->db->affected_rows);
    }
}

/**
 * Start a secure, DB-backed session.
 * Call this instead of session_start() throughout the application.
 */
function startSecureSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return; // Already started
    }

    $handler = new DatabaseSessionHandler();
    session_set_save_handler($handler, true);

    // Security hardening
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', '7200');

    // Mark cookie as Secure in production
    $isProduction = (getenv('APP_ENV') === 'production' || getenv('VERCEL') !== false);
    if ($isProduction) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}
