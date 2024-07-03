<?php

namespace Builder\Application;

use InvalidArgumentException;

class QueryBuilder
{
    private string $table;
    private readonly string $driver;
    private array $statements = [];
    private array $parameters = [];

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    public function getStatement(string $statement): mixed
    {
        return !empty($this->statements[$statement]) ? $this->statements[$statement] : null;
    }

    public function setStatement(string $statement, mixed $parameters): void
    {
        $this->statements[$statement] = $parameters;
    }

    public function setParameter(array $parameters): void
    {
        $data = [];
        foreach ($parameters as $key => $value) {
            $data[str_contains($key, ':') ? $key : ':' . $key] = $value;
        }

        $this->parameters = array_merge($this->parameters, $data);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setJoin(string $type, string $table, string $conditions): void
    {
        $validJoins = ['INNER', 'LEFT', 'RIGHT'];
        $type = strtoupper($type);

        if (!in_array($type, $validJoins)) {
            throw new InvalidArgumentException("Invalid JOIN type '$type' provided. Valid types are: " . implode(', ', $validJoins));
        }

        $joins = $this->getStatement('join') ?? [];
        $this->setStatement('join', array_merge($joins, ["$type JOIN $table ON $conditions"]));
    }

    public function setLimit(int $limit): void
    {
        $this->setStatement('limit', $limit);
    }

    public function setOffset(int $offset): void
    {
        $this->setStatement('offset', $offset);
    }

    public function setOrder(string $column, string $direction): void
    {
        $validDirections = ['ASC', 'DESC'];
        $direction = strtoupper($direction);

        if (!in_array($direction, $validDirections)) {
            throw new InvalidArgumentException("Invalid direction '$direction' provided. Valid directions are: " . implode(', ', $validDirections));
        }

        $order = ['column' => $column, 'direction' => strtoupper($direction)];
        $this->setStatement('order', $order);
    }

    public function buildQuery(): string
    {
        $parts = [
            $this->buildSelect(),
            $this->buildJoins(),
            $this->buildWhere(),
            $this->buildOrderBy(),
            $this->buildLimitOffset()
        ];

        return implode(' ', array_filter($parts));
    }

    public function reset(): void
    {
        $this->statements = [];
        $this->parameters = [];
    }

    private function buildSelect(): string
    {
        return (
            $this->getStatement('distinct') ? 'SELECT DISTINCT ' : 'SELECT '
        ) . implode(', ', $this->getStatement('fields')) . ' FROM ' . $this->table;
    }

    private function buildJoins(): string
    {
        if (! $this->getStatement('join')) {
            return "";
        }
        return implode(' ', $this->getStatement('join'));
    }

    private function buildWhere(): string
    {
        return !empty($this->getStatement('where')) ? "WHERE {$this->getStatement('where')}" : '';
    }

    private function buildOrderBy(): string
    {
        $order = $this->getStatement('order');
        if (empty($order)) {
            return '';
        }

        return "ORDER BY {$order['column']} {$order['direction']}";
    }

    private function buildLimitOffset(): string
    {
        if (empty($this->getStatement('limit'))) {
            return '';
        }

        if ($this->driver === 'sqlsrv') {
            return $this->buildSqlSrvLimitOffset();
        }

        return $this->buildDefaultLimitOffset();
    }

    private function buildDefaultLimitOffset(): string
    {
        if (empty($this->getStatement('offset'))) {
            return "LIMIT {$this->getStatement('limit')}";
        }

        return "LIMIT {$this->getStatement('limit')} OFFSET {$this->getStatement('offset')}";
    }

    private function buildSqlSrvLimitOffset(): string
    {
        if (empty($this->getStatement('offset'))) {
            $this->setStatement('offset', 0);
        }

        $query = "OFFSET {$this->getStatement('offset')} ROWS FETCH NEXT {$this->getStatement('limit')} ROWS ONLY";
        if (empty($this->getStatement('order'))) {
            $query = "ORDER BY 1" . $query;
        }

        return $query;
    }
}