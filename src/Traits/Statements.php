<?php

namespace Builder\Application\Traits;

use PDO;
use PDOException;
use PDOStatement;

trait Statements
{
    private function bind(PDOStatement $stmt, array $parameters = []): void
    {
        $data = empty($parameters) ? $this->queryBuilder->getParameters() : $parameters;
        foreach ($data as $key => &$value) {
            $stmt->bindParam($key, $value, match(gettype($value)) {
                'integer' => PDO::PARAM_INT,
                'boolean' => PDO::PARAM_BOOL,
                'NULL' => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            });
        }
    }

    private function prepare(?string $statement = null, ?array $parameters = []): PDOStatement
    {
        if (!$stmt = $this->connection->prepare($statement ?? $this->getStatement())) {
            throw new PDOException("Error preparing SQL statement. Please verify the syntax and parameters.", 500);
        }

        $this->bind($stmt, $parameters);
        return $stmt;
    }
}