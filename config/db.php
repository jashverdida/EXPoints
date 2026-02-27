<?php
/**
 * Centralized Database Configuration
 *
 * Reads credentials from environment variables for cloud deployment (Vercel).
 * Falls back to .env file parsing for local development.
 *
 * Required environment variables for production (set in Vercel dashboard):
 *   DB_HOST   – Cloud MySQL host (e.g. PlanetScale / Railway / Supabase Proxy)
 *   DB_NAME   – Database name
 *   DB_USER   – Database username
 *   DB_PASS   – Database password
 *   DB_PORT   – (optional) Port, defaults to 3306
 */

// Load .env file for local development only when env vars are not already set.
if (!getenv('DB_HOST')) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$name, $value] = array_map('trim', explode('=', $line, 2));
                if (!getenv($name)) {
                    putenv("$name=$value");
                    $_ENV[$name]    = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

/**
 * Returns a mysqli connection using environment-configured credentials.
 * Returns null on failure (caller should check and handle the error).
 */
function getDBConnection(): ?mysqli
{
    $host   = getenv('DB_HOST') ?: '127.0.0.1';
    $dbname = getenv('DB_NAME') ?: 'expoints_db';
    $user   = getenv('DB_USER') ?: 'root';
    $pass   = getenv('DB_PASS') ?: '';
    $port   = (int)(getenv('DB_PORT') ?: 3306);

    try {
        $mysqli = new mysqli($host, $user, $pass, $dbname, $port);

        if ($mysqli->connect_error) {
            throw new Exception('Connection failed: ' . $mysqli->connect_error);
        }

        $mysqli->set_charset('utf8mb4');
        return $mysqli;
    } catch (Exception $e) {
        error_log('Database connection error: ' . $e->getMessage());
        return null;
    }
}
