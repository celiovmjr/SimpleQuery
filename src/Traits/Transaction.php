<?php

declare(strict_types=1);

namespace Builder\Application\Traits;

trait Transaction
{
    protected function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    protected function commit(): void
    {
        $this->connection->commit();
    }

    protected function rollBack(): void
    {
        $this->connection->rollBack();
    }
}
