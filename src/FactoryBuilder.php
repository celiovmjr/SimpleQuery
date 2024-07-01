<?php

namespace Builder\Application;

abstract  class FactoryBuilder
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

    protected function fromArray(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    protected function fromObject(object $data): static
    {
        $this->fromArray(get_object_vars($data));
        return $this;
    }

    protected function toArray(): array
    {
        return $this->data;
    }

    protected function toObject(): object
    {
        return (object) $this->data;
    }
}