<?php

declare(strict_types=1);

namespace App\Model;

class QueryParameterPageModel
{
    /**
     * @var ?array<int> $page
     */
    private ?array $page = null;

    /**
     * @param array<int>|null $page
     */
    public function __construct(?array $page)
    {
        $this->page = $page;
    }

    public function getNumber(): int
    {
        return true === isset($this->page['number']) ? intval($this->page['number']) : 1;
    }

    public function getSize(): int
    {
        return true === isset($this->page['size']) ? intval($this->page['size']) : 20;
    }
}
