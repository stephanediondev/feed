<?php

declare(strict_types=1);

namespace App\Model;

class ImportOpmlModel
{
    private ?string $file = null;

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }
}
