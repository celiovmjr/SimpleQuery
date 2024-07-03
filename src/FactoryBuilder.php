<?php

declare(strict_types=1);

namespace Builder\Application;

abstract class FactoryBuilder
{
    protected array $data = [];

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->data[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    public function fromArray(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function fromObject(object $data): static
    {
        $this->fromArray(get_object_vars($data));
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toObject(): object
    {
        return (object) $this->data;
    }
}
