<?php

declare(strict_types=1);

namespace Builder\Application\Traits;

use InvalidArgumentException;
use PDO;

trait Utils
{
    public function pluralize(string $word): string
    {
        $pluralRules = [
            '/(s)tatus$/i' => '\1tatuses',
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive)$/i' => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/(shea|lea|loa|thie)f$/i' => '\1ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(tomato)$/i' => '\1es',
            '/(bu)s$/i' => '\1ses',
            '/(alias|status)$/i' => '\1es',
            '/(octop|vir)us$/i' => '\1i',
            '/(ax|test)is$/i' => '\1es',
            '/s$/i' => 's',
            '/$/i' => 's'
        ];

        foreach ($pluralRules as $pattern => $replacement) {
            if (preg_match($pattern, $word)) {
                return strtolower(preg_replace($pattern, $replacement, $word));
            }
        }

        return strtolower($word) . 's';
    }

    private function required(): bool
    {
        foreach ($this->required as $column) {
            if (empty($this->$column) && !is_numeric($this->$column) && $this->$column !== '') {
                throw new InvalidArgumentException("Missing required column: $column");
            }
        }

        return true;
    }

    private function safe(): array
    {
        $unsetData = array_merge($this->safe, [$this->primaryKey]);
        foreach ($unsetData as $column) {
            unset($this->$column);
        }

        return $this->toArray() ?? [];
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
