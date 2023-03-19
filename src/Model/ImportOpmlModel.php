<?php

namespace App\Model;

class ImportOpmlModel
{
    private string $file;

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string$file): self
    {
        $this->file = $file;

        return $this;
    }
}
