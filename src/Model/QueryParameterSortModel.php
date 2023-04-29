<?php

declare(strict_types=1);

namespace App\Model;

class QueryParameterSortModel
{
    private ?string $sort = null;

    public function __construct(mixed $sort)
    {
        $this->sort = $sort;
    }

    public function raw(): ?string
    {
        return $this->sort;
    }

    /**
     * @return array<string>
     */
    public function get(): ?array
    {
        if (null === $this->sort) {
            return null;
        }

        $criterias = [];
        $values = explode(',', urldecode($this->sort));
        foreach ($values as $value) {
            $criteria = [];
            $value = trim($value);
            if (str_starts_with($value, '-')) {
                $criteria['direction'] = 'DESC';
                $criteria['field'] = substr($value, 1);
            } else {
                $criteria['direction'] = 'ASC';
                $criteria['field'] = $value;
            }
            $criterias[] = $criteria;
        }

        return $criterias[0] ?? null;
    }
}
