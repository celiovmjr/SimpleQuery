<?php

declare(strict_types=1);

namespace Builder\Application;

use InvalidArgumentException;

class QueryBuilder
{
    private array $statements = [];
    private array $parameters = [];

    public function __construct(
        private string $table,
        private readonly string $driver
    ) {
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
            $data[str_contains($key, ':') ? $key : ":{$key}"] = $value;
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

        if (!in_array($direction, $validDirections, true)) {
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
            $this->getStatement('distinct') ? "SELECT DISTINCT " : "SELECT "
        ) . implode(', ', $this->getStatement('fields') ?? []) . " FROM $this->table";
    }

    private function buildJoins(): string
    {
        return $this->getStatement('join') ? implode(' ', $this->getStatement('join')) : '';
    }

    private function buildWhere(): string
    {
        return $this->getStatement('where') ? "WHERE {$this->getStatement('where')}" : '';
    }

    private function buildOrderBy(): string
    {
        $order = $this->getStatement('order');
        return $order ? "ORDER BY {$order['column']} {$order['direction']}" : '';
    }

    private function buildLimitOffset(): string
    {
        if (empty($this->getStatement('limit'))) {
            return '';
        }

        return $this->driver === 'sqlsrv'
            ? $this->buildSqlSrvLimitOffset()
            : $this->buildDefaultLimitOffset();
    }

    private function buildDefaultLimitOffset(): string
    {
        $limit = $this->getStatement('limit');
        $offset = $this->getStatement('offset');
        return $offset !== null
            ? "LIMIT {$limit} OFFSET {$offset}"
            : "LIMIT {$limit}";
    }

    private function buildSqlSrvLimitOffset(): string
    {
        $offset = $this->getStatement('offset') ?? 0;
        $limit = $this->getStatement('limit') ?? 'ALL';

        $query = "OFFSET {$offset} ROWS FETCH NEXT {$limit} ROWS ONLY";
        return empty($this->getStatement('order'))
            ? "ORDER BY 1 $query"
            : $query;
    }
}
