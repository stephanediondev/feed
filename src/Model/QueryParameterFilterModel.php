<?php

declare(strict_types=1);

namespace App\Model;

class QueryParameterFilterModel
{
    /**
     * @var ?array<mixed> $filters
     */
    private ?array $filters = null;

    /**
     * @param array<mixed> $filters
     */
    public function __construct(?array $filters)
    {
        $this->filters = $filters;
    }

    public function get(string $key): mixed
    {
        return $this->filters[$key] ?? null;
    }

    public function getBool(string $key): mixed
    {
        return true === isset($this->filters[$key]) && true === in_array(strtolower($this->filters[$key]), ['true', '1']) ? true : false;
    }

    public function getInt(string $key): mixed
    {
        return true === isset($this->filters[$key]) ? intval($this->filters[$key]) : null;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->filters ?? [];
    }
}
