<?php

namespace App\Models;

use App\Models\QueryBuilder;

abstract class Model {
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    protected array $fillable = [];
    protected array $relationships = [];

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }

    public function fill(array $attributes): void {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
    }

    public function setAttribute(string $key, $value): void {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key) {
        return $this->attributes[$key] ?? null;
    }

    public function getId(): ?int {
        return $this->getAttribute(static::$primaryKey);
    }

    public function save(): bool {
        if ($this->exists) {
            return $this->update();
        }
        return $this->insert();
    }

    protected function insert(): bool {
        $attributes = $this->getDirty();
        if (empty($attributes)) {
            return true;
        }

        $columns = implode(', ', array_keys($attributes));
        $values = implode(', ', array_fill(0, count($attributes), '?'));
        
        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($values)";
        
        $result = Database::getInstance()->query($sql, array_values($attributes));
        
        if ($result) {
            $this->exists = true;
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }

    protected function update(): bool {
        $attributes = $this->getDirty();
        if (empty($attributes)) {
            return true;
        }

        $set = [];
        foreach (array_keys($attributes) as $column) {
            $set[] = "$column = ?";
        }
        
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $set) . 
               " WHERE " . static::$primaryKey . " = ?";
        
        $values = array_values($attributes);
        $values[] = $this->getAttribute(static::$primaryKey);
        
        $result = Database::getInstance()->query($sql, $values);
        
        if ($result) {
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }

    public function delete(): bool {
        if (!$this->exists) {
            return false;
        }

        $sql = "DELETE FROM " . static::$table . 
               " WHERE " . static::$primaryKey . " = ?";
        
        $result = Database::getInstance()->query($sql, [$this->getAttribute(static::$primaryKey)]);
        
        if ($result) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }

    public function getDirty(): array {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    public static function find($id): ?self {
        try {
            return self::where('id', '=', $id)->first();
        } catch (\Exception $e) {
            error_log("Error finding record in " . static::$table . ": " . $e->getMessage());
            throw $e;
        }
    }

    public static function all(): array {
        $sql = "SELECT * FROM " . static::$table;
        $result = Database::getInstance()->query($sql);
        
        $models = [];
        while ($row = $result->fetch_assoc()) {
            $model = new static($row);
            $model->exists = true;
            $model->original = $row;
            $models[] = $model;
        }
        
        return $models;
    }

    public static function where(string $column, string $operator, $value): QueryBuilder {
        return (new QueryBuilder(static::class))->where($column, $operator, $value);
    }

    public function __get(string $key) {
        if (array_key_exists($key, $this->relationships)) {
            return $this->loadRelationship($key);
        }
        return $this->getAttribute($key);
    }

    public function __set(string $key, $value) {
        $this->setAttribute($key, $value);
    }

    protected function loadRelationship(string $key) {
        $relationship = $this->relationships[$key];
        $foreignKey = $key . '_id';
        $id = $this->getAttribute($foreignKey);
        
        if ($id === null) {
            return null;
        }
        
        return $relationship::find($id);
    }

    public static function count(): int {
        return (new QueryBuilder(static::class))->count();
    }

    public static function sum(string $column): float {
        return (new QueryBuilder(static::class))->sum($column);
    }

    public static function orderBy(string $column, string $direction = 'ASC'): QueryBuilder {
        return (new QueryBuilder(static::class))->orderBy($column, $direction);
    }

    public static function limit(int $limit): QueryBuilder {
        return (new QueryBuilder(static::class))->limit($limit);
    }

    public static function get(): array {
        return (new QueryBuilder(static::class))->get();
    }
} 