<?php

namespace App\Services;

class SupabaseQueryBuilder
{
    protected SupabaseService $service;
    protected string $table;
    protected string $columns = '*';
    protected array $filters = [];
    protected ?string $orderBy = null;
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(SupabaseService $service, string $table)
    {
        $this->service = $service;
        $this->table = $table;
    }

    /**
     * Select specific columns.
     */
    public function select(string $columns = '*'): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Add a where clause (equality).
     */
    public function where(string $column, $value): self
    {
        $this->filters[$column] = $value;
        return $this;
    }

    /**
     * Add a where clause with operator.
     * Supported operators: eq, neq, gt, gte, lt, lte, like, ilike, is, in
     */
    public function whereOp(string $column, string $operator, $value): self
    {
        $this->filters[$column . '.' . $operator] = $value;
        return $this;
    }

    /**
     * Where column is null.
     */
    public function whereNull(string $column): self
    {
        $this->filters[$column . '.is'] = 'null';
        return $this;
    }

    /**
     * Where column is not null.
     */
    public function whereNotNull(string $column): self
    {
        $this->filters[$column . '.not.is'] = 'null';
        return $this;
    }

    /**
     * Where column is in array.
     */
    public function whereIn(string $column, array $values): self
    {
        $this->filters[$column . '.in'] = '(' . implode(',', $values) . ')';
        return $this;
    }

    /**
     * Where column is greater than value.
     */
    public function whereGt(string $column, $value): self
    {
        return $this->whereOp($column, 'gt', $value);
    }

    /**
     * Where column is greater than or equal to value.
     */
    public function whereGte(string $column, $value): self
    {
        return $this->whereOp($column, 'gte', $value);
    }

    /**
     * Where column is less than value.
     */
    public function whereLt(string $column, $value): self
    {
        return $this->whereOp($column, 'lt', $value);
    }

    /**
     * Where column is less than or equal to value.
     */
    public function whereLte(string $column, $value): self
    {
        return $this->whereOp($column, 'lte', $value);
    }

    /**
     * Where column is not equal to value.
     */
    public function whereNot(string $column, $value): self
    {
        return $this->whereOp($column, 'neq', $value);
    }

    /**
     * Where column matches pattern (case-sensitive LIKE).
     */
    public function whereLike(string $column, string $pattern): self
    {
        return $this->whereOp($column, 'like', $pattern);
    }

    /**
     * Where column matches pattern (case-insensitive ILIKE).
     */
    public function whereILike(string $column, string $pattern): self
    {
        return $this->whereOp($column, 'ilike', $pattern);
    }

    /**
     * Order results.
     *
     * @param string $column Column to order by
     * @param string $direction 'asc' or 'desc'
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orderBy = $column . '.' . $direction;
        return $this;
    }

    /**
     * Order by descending.
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Limit results.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Offset results (for pagination).
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Paginate results.
     */
    public function paginate(int $page, int $perPage = 15): self
    {
        $this->limit = $perPage;
        $this->offset = ($page - 1) * $perPage;
        return $this;
    }

    /**
     * Execute the query and get results.
     */
    public function get(): array
    {
        $options = [];

        if ($this->orderBy) {
            $options['order'] = $this->orderBy;
        }

        if ($this->limit !== null) {
            $options['limit'] = $this->limit;
        }

        if ($this->offset !== null) {
            $options['offset'] = $this->offset;
        }

        return $this->service->select($this->table, $this->columns, $this->filters, $options);
    }

    /**
     * Get the first result.
     */
    public function first(): ?array
    {
        $this->limit = 1;
        $results = $this->get();

        return $results[0] ?? null;
    }

    /**
     * Get the count of results.
     */
    public function count(): int
    {
        return $this->service->count($this->table, $this->filters);
    }

    /**
     * Check if any records exist.
     */
    public function exists(): bool
    {
        return $this->service->exists($this->table, $this->filters);
    }

    /**
     * Insert a record.
     */
    public function insert(array $data): array
    {
        return $this->service->insert($this->table, $data);
    }

    /**
     * Update records matching filters.
     */
    public function update(array $data): array
    {
        return $this->service->update($this->table, $data, $this->filters);
    }

    /**
     * Delete records matching filters.
     */
    public function delete(): array
    {
        return $this->service->delete($this->table, $this->filters);
    }
}
