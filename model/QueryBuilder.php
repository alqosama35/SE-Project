<?php

namespace App\Models;

class QueryBuilder {
    private string $model;
    private array $wheres = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $selects = ['*'];
    private array $joins = [];
    
    public function __construct(string $model) {
        $this->model = $model;
    }
    
    public function select(array $columns): self {
        $this->selects = $columns;
        return $this;
    }
    
    public function where(string $column, string $operator, $value): self {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        return $this;
    }
    
    public function orWhere(string $column, string $operator, $value): self {
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        return $this;
    }
    
    public function orWhereNull(string $column): self {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IS',
            'value' => 'NULL',
            'boolean' => 'OR'
        ];
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }
    
    public function limit(int $limit): self {
        $this->limit = $limit;
        return $this;
    }
    
    public function offset(int $offset): self {
        $this->offset = $offset;
        return $this;
    }
    
    public function join(string $table, string $first, string $operator, string $second): self {
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => 'INNER'
        ];
        return $this;
    }
    
    public function leftJoin(string $table, string $first, string $operator, string $second): self {
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => 'LEFT'
        ];
        return $this;
    }
    
    public function get(): array {
        $sql = $this->toSql();
        $params = $this->getBindings();
        
        $result = Database::getInstance()->query($sql, $params);
        
        $models = [];
        while ($row = $result->fetch_assoc()) {
            $model = new $this->model($row);
            $model->exists = true;
            $model->original = $row;
            $models[] = $model;
        }
        
        return $models;
    }
    
    public function first(): ?Model {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }
    
    public function count(): int {
        $this->selects = ['COUNT(*) as count'];
        $result = $this->first();
        return $result ? (int)$result->count : 0;
    }
    
    public function sum(string $column): float {
        $this->selects = ["SUM($column) as sum"];
        $result = $this->first();
        return $result ? (float)$result->sum : 0.0;
    }
    
    private function toSql(): string {
        $table = constant($this->model . '::$table');
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM " . $table;
        
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $conditions = [];
            foreach ($this->wheres as $where) {
                $conditions[] = "{$where['boolean']} {$where['column']} {$where['operator']} ?";
            }
            $sql .= implode(' ', $conditions);
        }
        
        if (!empty($this->orders)) {
            $sql .= " ORDER BY ";
            $orders = [];
            foreach ($this->orders as $order) {
                $orders[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= implode(', ', $orders);
        }
        
        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }
        
        return $sql;
    }
    
    private function getBindings(): array {
        $bindings = [];
        foreach ($this->wheres as $where) {
            $bindings[] = $where['value'];
        }
        return $bindings;
    }
} 