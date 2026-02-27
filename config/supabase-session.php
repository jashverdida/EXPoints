<?php
/**
 * Supabase-backed PHP Session Handler
 *
 * Stores sessions in Supabase via REST API — works on Vercel serverless
 * (no filesystem persistence or MySQL required).
 *
 * Falls back to native PHP file sessions when SUPABASE_SERVICE_KEY is absent
 * so local development works without Supabase credentials.
 */

// Load .env for local development (no-op if already loaded via env.php)
if (!getenv('SUPABASE_URL')) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            if (!getenv($k)) { putenv("$k=$v"); $_ENV[$k] = $v; $_SERVER[$k] = $v; }
        }
    }
}

class SupabaseSessionHandler implements SessionHandlerInterface
{
    private string $url;
    private string $serviceKey;
    private int    $lifetime;

    public function __construct(string $url, string $serviceKey, int $lifetime = 7200)
    {
        $this->url        = rtrim($url, '/');
        $this->serviceKey = $serviceKey;
        $this->lifetime   = $lifetime;
    }

    private function req(string $method, string $path, ?array $body = null): array
    {
        $ch      = curl_init($this->url . '/rest/v1/' . $path);
        $headers = [
            'apikey: '               . $this->serviceKey,
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: application/json',
        ];
        if ($method === 'POST') {
            // Upsert: merge on PK conflict
            $headers[] = 'Prefer: resolution=merge-duplicates,return=minimal';
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 5,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $resp   = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['status' => $status, 'body' => (string) $resp];
    }

    public function open(string $path, string $name): bool { return true; }
    public function close(): bool                          { return true; }

    public function read(string $id): string
    {
        $now    = gmdate('Y-m-d\TH:i:s\Z');
        $result = $this->req('GET',
            'php_sessions?session_id=eq.' . rawurlencode($id) .
            '&expires_at=gt.'             . rawurlencode($now) .
            '&select=session_data&limit=1'
        );
        if ($result['status'] !== 200) return '';
        $rows = json_decode($result['body'], true);
        return (!empty($rows) && isset($rows[0]['session_data']))
            ? (string) $rows[0]['session_data']
            : '';
    }

    public function write(string $id, string $data): bool
    {
        $expires = gmdate('Y-m-d\TH:i:s\Z', time() + $this->lifetime);
        $this->req('POST', 'php_sessions', [
            'session_id'   => $id,
            'session_data' => $data,
            'expires_at'   => $expires,
        ]);
        return true;
    }

    public function destroy(string $id): bool
    {
        $this->req('DELETE', 'php_sessions?session_id=eq.' . rawurlencode($id));
        return true;
    }

    public function gc(int $max_lifetime): int
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');
        $this->req('DELETE', 'php_sessions?expires_at=lt.' . rawurlencode($now));
        return 0;
    }
}

/**
 * Start a secure, Supabase-backed session.
 * Safe to call multiple times — no-op if session is already active.
 * Falls back to native file sessions when Supabase credentials are absent.
 */
function startSecureSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return; // already started
    }

    $url        = getenv('SUPABASE_URL');
    $serviceKey = getenv('SUPABASE_SERVICE_KEY');

    if ($url && $serviceKey) {
        $lifetime = (int) (getenv('SESSION_LIFETIME') ?: 7200);
        session_set_save_handler(
            new SupabaseSessionHandler($url, $serviceKey, $lifetime),
            true
        );
    }
    // No creds → native file sessions (local dev fallback)

    ini_set('session.use_strict_mode',  '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly',  '1');
    ini_set('session.cookie_samesite',  'Lax');
    ini_set('session.gc_maxlifetime',   '7200');

    if (getenv('APP_ENV') === 'production') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}

// Auto-start on include
startSecureSession();
