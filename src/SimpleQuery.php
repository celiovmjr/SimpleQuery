<?php

declare(strict_types=1);

namespace Builder\Application;

use Builder\Application\Traits\Transaction;
use Builder\Application\Traits\Statement;
use Builder\Application\Traits\Utils;
use Builder\Application\Traits\CRUD;
use PDO;

abstract class SimpleQuery extends FactoryBuilder
{
    use Transaction;
    use Statement;
    use Utils;
    use CRUD;

    protected string $table;
    protected string $primaryKey = 'id';
    protected array $required = [];
    protected array $safe = [];
    private readonly QueryBuilder $queryBuilder;

    public function __construct(private readonly PDO $connection)
    {
        $this->queryBuilder = new QueryBuilder();
        $this->queryBuilder->setTable($this->table());
        $this->queryBuilder->setDriver($this->driver());
    }

    protected function select(array $fields = ['*'], bool $distinct = false): self
    {
        $this->queryBuilder->reset();
        $this->queryBuilder->setStatement('fields', $fields);
        $this->queryBuilder->setStatement('distinct', $distinct);
        return $this;
    }

    protected function from(string $table): self
    {
        $this->queryBuilder->setTable($table);
        return $this;
    }

    protected function where(string $condition, array $parameters = []): self
    {
        if (empty($this->queryBuilder->getStatement('where'))) {
            $this->queryBuilder->setStatement('where', $condition);
            $this->queryBuilder->setParameter($parameters);
            return $this;
        }

        $condition = $this->queryBuilder->getStatement('where') . ' ' . $condition;
        $this->queryBuilder->setStatement('where', $condition);
        $this->queryBuilder->setParameter($parameters);
        return $this;
    }

    protected function inner(string $table, string $conditions): self
    {
        $this->queryBuilder->setJoin('INNER', $table, $conditions);
        return $this;
    }

    protected function left(string $table, string $conditions): self
    {
        $this->queryBuilder->setJoin('LEFT', $table, $conditions);
        return $this;
    }

    protected function right(string $table, string $conditions): self
    {
        $this->queryBuilder->setJoin('RIGHT', $table, $conditions);
        return $this;
    }

    protected function asc(string $column): self
    {
        $this->queryBuilder->setOrder($column, 'ASC');
        return $this;
    }

    protected function desc(string $column): self
    {
        $this->queryBuilder->setOrder($column, 'DESC');
        return $this;
    }

    protected function limit(int $limit): self
    {
        $this->queryBuilder->setLimit($limit);
        return $this;
    }

    protected function offset(int $offset): self
    {
        $this->queryBuilder->setOffset($offset);
        return $this;
    }

    protected function getStatement(): string
    {
        return $this->queryBuilder->buildQuery();
    }

    private function table(): string
    {
        if (empty($this->table)) {
            $table = explode('\\', get_called_class());
            return $this->pluralize(end($table));
        }

        return $this->table;
    }

    private function driver(): ?string
    {
        return $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}
