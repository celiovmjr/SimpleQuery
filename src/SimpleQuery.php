<?php declare(strict_types=1);

namespace Builder\Application;

use Builder\Application\Traits\CRUD;
use Builder\Application\Traits\Statements;
use Builder\Application\Traits\Utils;
use PDO;

abstract class SimpleQuery extends FactoryBuilder
{
    use CRUD, Statements, Utils;

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

    public function select(array $fields = ['*'], bool $distinct = false): self
    {
        $this->queryBuilder->setStatement('fields', $fields);
        $this->queryBuilder->setStatement('distinct', $distinct);
        return $this;
    }

    public function from(string $table): self
    {
        $this->queryBuilder->setTable($table);
        return $this;
    }

    public function where(string $condition, array $parameters = []): self
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

    public function inner(string $table, string $conditions): self
    {
        $this->queryBuilder->setJoin('INNER', $table, $conditions);
        return $this;
    }

    public function left(string $table, string $conditions): self
    {
        $this->queryBuilder->setJoin('LEFT', $table, $conditions);
        return $this;
    }

    public function right(string $table, string $conditions): self
    {
        $this->queryBuilder->setJoin('RIGHT', $table, $conditions);
        return $this;
    }

    public function asc(string $column): self
    {
        $this->queryBuilder->setOrder($column, 'ASC');
        return $this;
    }

    public function desc(string $column): self
    {
        $this->queryBuilder->setOrder($column, 'DESC');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->queryBuilder->setLimit($limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->queryBuilder->setOffset($offset);
        return $this;
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function getStatement(): string
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