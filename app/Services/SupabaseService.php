<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Exception;

class SupabaseService
{
    protected string $url;
    protected string $anonKey;
    protected string $serviceKey;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->anonKey = config('supabase.anon_key');
        $this->serviceKey = config('supabase.service_key');
        $this->timeout = config('supabase.timeout', 30);
    }

    /**
     * Get the REST API base URL for a table.
     */
    protected function getTableUrl(string $table): string
    {
        return rtrim($this->url, '/') . '/rest/v1/' . $table;
    }

    /**
     * Get default headers for Supabase requests.
     */
    protected function getHeaders(bool $useServiceKey = false): array
    {
        $key = $useServiceKey ? $this->serviceKey : $this->anonKey;

        return [
            'apikey' => $key,
            'Authorization' => 'Bearer ' . $key,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ];
    }

    /**
     * Check if Supabase is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->url) && !empty($this->anonKey);
    }

    /**
     * Select records from a table.
     *
     * @param string $table Table name
     * @param string $columns Columns to select (default: *)
     * @param array $filters Array of filters ['column' => 'value'] or ['column.operator' => 'value']
     * @param array $options Additional options: order, limit, offset
     * @return array
     */
    public function select(string $table, string $columns = '*', array $filters = [], array $options = []): array
    {
        $url = $this->getTableUrl($table);
        $params = ['select' => $columns];

        // Apply filters
        foreach ($filters as $key => $value) {
            if (strpos($key, '.') !== false) {
                // Filter with operator: column.operator => value
                $params[$key] = $value;
            } else {
                // Simple equality filter
                $params[$key] = 'eq.' . $value;
            }
        }

        // Apply ordering
        if (isset($options['order'])) {
            $params['order'] = $options['order'];
        }

        // Apply limit
        if (isset($options['limit'])) {
            $params['limit'] = $options['limit'];
        }

        // Apply offset
        if (isset($options['offset'])) {
            $params['offset'] = $options['offset'];
        }

        $response = Http::withHeaders($this->getHeaders())
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->get($url, $params);

        return $this->handleResponse($response);
    }

    /**
     * Select a single record by ID.
     */
    public function find(string $table, int $id, string $columns = '*'): ?array
    {
        $results = $this->select($table, $columns, ['id' => $id], ['limit' => 1]);

        return $results[0] ?? null;
    }

    /**
     * Select a single record by a specific column.
     */
    public function findBy(string $table, string $column, $value, string $columns = '*'): ?array
    {
        $results = $this->select($table, $columns, [$column => $value], ['limit' => 1]);

        return $results[0] ?? null;
    }

    /**
     * Insert a new record.
     *
     * @param string $table Table name
     * @param array $data Data to insert
     * @return array Inserted record
     */
    public function insert(string $table, array $data): array
    {
        $url = $this->getTableUrl($table);

        $response = Http::withHeaders($this->getHeaders(true))
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->post($url, $data);

        $result = $this->handleResponse($response);

        return $result[0] ?? $result;
    }

    /**
     * Insert multiple records.
     */
    public function insertMany(string $table, array $records): array
    {
        $url = $this->getTableUrl($table);

        $response = Http::withHeaders($this->getHeaders(true))
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->post($url, $records);

        return $this->handleResponse($response);
    }

    /**
     * Update records matching filters.
     *
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $filters Filters to match records
     * @return array Updated records
     */
    public function update(string $table, array $data, array $filters): array
    {
        $url = $this->getTableUrl($table);
        $params = [];

        foreach ($filters as $key => $value) {
            if (strpos($key, '.') !== false) {
                $params[$key] = $value;
            } else {
                $params[$key] = 'eq.' . $value;
            }
        }

        $response = Http::withHeaders($this->getHeaders(true))
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->patch($url . '?' . http_build_query($params), $data);

        return $this->handleResponse($response);
    }

    /**
     * Update a record by ID.
     */
    public function updateById(string $table, int $id, array $data): ?array
    {
        $results = $this->update($table, $data, ['id' => $id]);

        return $results[0] ?? null;
    }

    /**
     * Delete records matching filters.
     *
     * @param string $table Table name
     * @param array $filters Filters to match records
     * @return array Deleted records
     */
    public function delete(string $table, array $filters): array
    {
        $url = $this->getTableUrl($table);
        $params = [];

        foreach ($filters as $key => $value) {
            if (strpos($key, '.') !== false) {
                $params[$key] = $value;
            } else {
                $params[$key] = 'eq.' . $value;
            }
        }

        $response = Http::withHeaders($this->getHeaders(true))
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->delete($url . '?' . http_build_query($params));

        return $this->handleResponse($response);
    }

    /**
     * Delete a record by ID.
     */
    public function deleteById(string $table, int $id): ?array
    {
        $results = $this->delete($table, ['id' => $id]);

        return $results[0] ?? null;
    }

    /**
     * Count records in a table.
     */
    public function count(string $table, array $filters = []): int
    {
        $url = $this->getTableUrl($table);
        $params = ['select' => 'count'];

        foreach ($filters as $key => $value) {
            if (strpos($key, '.') !== false) {
                $params[$key] = $value;
            } else {
                $params[$key] = 'eq.' . $value;
            }
        }

        $headers = $this->getHeaders();
        $headers['Prefer'] = 'count=exact';

        $response = Http::withHeaders($headers)
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->head($url, $params);

        $contentRange = $response->header('content-range');
        if ($contentRange && preg_match('/\/(\d+)$/', $contentRange, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Check if a record exists.
     */
    public function exists(string $table, array $filters): bool
    {
        $results = $this->select($table, 'id', $filters, ['limit' => 1]);

        return !empty($results);
    }

    /**
     * Execute a raw query using RPC (stored procedure).
     */
    public function rpc(string $functionName, array $params = []): array
    {
        $url = rtrim($this->url, '/') . '/rest/v1/rpc/' . $functionName;

        $response = Http::withHeaders($this->getHeaders(true))
            ->connectTimeout(3)
            ->timeout($this->timeout)
            ->post($url, $params);

        return $this->handleResponse($response);
    }

    /**
     * Handle API response and throw exceptions on errors.
     */
    protected function handleResponse(Response $response): array
    {
        if ($response->failed()) {
            $error = $response->json();
            $message = $error['message'] ?? $error['error'] ?? 'Unknown Supabase error';
            $code = $error['code'] ?? $response->status();

            throw new Exception("Supabase API error ({$code}): {$message}");
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    /**
     * Build a query with advanced filters.
     */
    public function query(string $table): SupabaseQueryBuilder
    {
        return new SupabaseQueryBuilder($this, $table);
    }
}
