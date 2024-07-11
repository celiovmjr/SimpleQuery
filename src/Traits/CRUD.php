<?php

declare(strict_types=1);

namespace Builder\Application\Traits;

use PDO;
use PDOException;
use DateTime;

trait CRUD
{
    protected function fetch(bool $all = false, bool $associative = false): array|static|null
    {
        try {
            $stmt = $this->prepare();
            $stmt->execute();

            if (! $stmt->rowCount()) {
                return null;
            }

            if ($all && $associative) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if ($all) {
                return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
            }

            if ($associative) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return $stmt->fetchObject(static::class);
        } catch (PDOException) {
            throw new PDOException("Failed to retrieve data from the database.", 500);
        }
    }

    protected function save(): bool
    {
        if (empty($this->{$this->primaryKey})) {
            return $this->create();
        }

        return $this->update();
    }

    protected function delete(int|string $id): bool
    {
        try {
            $stmt = $this->prepare("DELETE FROM {$this->table()} WHERE $this->primaryKey=:id", ['id' => $id]);
            return $stmt->execute();
        } catch (PDOException) {
            throw new PDOException("Failed to delete data from the database.", 500);
        }
    }

    private function create(): bool
    {
        try {
            if (! $this->required()) {
                return false;
            }


            $columns = implode(', ', array_keys($this->safe()));
            $placeholders = implode(', ', array_map(fn ($col) => ":$col", array_keys($this->safe())));
            $query = "INSERT INTO {$this->table()} ($columns) VALUES ($placeholders)";

            $this->connection->beginTransaction();
            $stmt = $this->prepare($query, $this->safe());

            if (! $stmt->execute()) {
                $this->connection->rollBack();
                return false;
            }

            $this->connection->commit();
            return true;
        } catch (PDOException) {
            $this->connection->rollBack();
            throw new PDOException("Failed to create record.", 500);
        }
    }

    private function update(): bool
    {
        try {
			if ($this->timeStamp) {
                $this->updated_at = (new DateTime("now"))->format("Y-m-d H:i:s");
            }

            if (!$this->required()) {
                return false;
            }

            $primaryKeyValue = $this->{$this->primaryKey};
            $setClause = implode(', ', array_map(fn ($col) => "$col=:$col", array_keys($this->safe())));
			$query = "UPDATE {$this->table()} SET $setClause WHERE $this->primaryKey=:$this->primaryKey";

            $this->connection->beginTransaction();
            $stmt = $this->prepare($query, $this->safe());
            $stmt->bindParam(":$this->primaryKey", $primaryKeyValue);

            if (! $stmt->execute()) {
                $this->connection->rollBack();
                return false;
            }

            $this->connection->commit();
            return true;
        } catch (PDOException) {
            $this->connection->rollBack();
            throw new PDOException("Failed to update record.", 500);
        }
    }
}
