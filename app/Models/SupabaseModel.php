<?php

namespace App\Models;

use App\Services\SupabaseService;
use App\Services\SupabaseQueryBuilder;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use ArrayAccess;
use JsonSerializable;

abstract class SupabaseModel implements ArrayAccess, JsonSerializable
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    /**
     * Get the Supabase service instance.
     */
    protected static function getService(): SupabaseService
    {
        return app(SupabaseService::class);
    }

    /**
     * Get the table name.
     */
    public static function getTable(): string
    {
        return static::$table;
    }

    /**
     * Start a new query.
     */
    public static function query(): SupabaseQueryBuilder
    {
        return static::getService()->query(static::$table);
    }

    /**
     * Find a record by ID.
     */
    public static function find(int $id): ?static
    {
        $data = static::getService()->find(static::$table, $id);

        if ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }

        return null;
    }

    /**
     * Find a record by ID or throw exception.
     */
    public static function findOrFail(int $id): static
    {
        $model = static::find($id);

        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }

        return $model;
    }

    /**
     * Get all records.
     */
    public static function all(): array
    {
        $results = static::getService()->select(static::$table);

        return array_map(function ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }, $results);
    }

    /**
     * Get records with conditions.
     */
    public static function where(string $column, $value): SupabaseQueryBuilder
    {
        return static::query()->where($column, $value);
    }

    /**
     * Create a new record.
     */
    public static function create(array $attributes): static
    {
        $data = static::getService()->insert(static::$table, $attributes);

        $model = new static($data);
        $model->exists = true;

        return $model;
    }

    /**
     * Save the model.
     */
    public function save(): bool
    {
        if ($this->exists) {
            // Update existing record
            $dirty = $this->getDirty();

            if (empty($dirty)) {
                return true;
            }

            $data = static::getService()->updateById(
                static::$table,
                $this->getKey(),
                $dirty
            );

            if ($data) {
                $this->fill($data);
                $this->original = $this->attributes;
                return true;
            }

            return false;
        }

        // Insert new record
        $data = static::getService()->insert(static::$table, $this->attributes);

        if ($data) {
            $this->fill($data);
            $this->original = $this->attributes;
            $this->exists = true;
            return true;
        }

        return false;
    }

    /**
     * Update the model.
     */
    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Delete the model.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $result = static::getService()->deleteById(static::$table, $this->getKey());

        if ($result !== null) {
            $this->exists = false;
            return true;
        }

        return false;
    }

    /**
     * Fill the model with attributes.
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the primary key value.
     */
    public function getKey(): ?int
    {
        return $this->attributes[static::$primaryKey] ?? null;
    }

    /**
     * Get an attribute value.
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get dirty (changed) attributes.
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Check if the model has been modified.
     */
    public function isDirty(): bool
    {
        return !empty($this->getDirty());
    }

    /**
     * Get all attributes.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * JsonSerializable implementation.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Magic getter.
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter.
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic isset.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * ArrayAccess implementation.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Refresh the model from the database.
     */
    public function refresh(): self
    {
        if ($this->exists) {
            $data = static::getService()->find(static::$table, $this->getKey());
            if ($data) {
                $this->fill($data);
                $this->original = $this->attributes;
            }
        }

        return $this;
    }

    /**
     * Helper to get results as model instances.
     */
    public static function hydrate(array $results): array
    {
        return array_map(function ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }, $results);
    }
}
