<?php

namespace App\Trait\Entity;

use App\Helper\ExtraFieldsHelper;
use Doctrine\ORM\Mapping as ORM;

trait ExtraFieldsEntityTrait
{
    #[ORM\Column(name: "extra_fields", type: "json", nullable: true)]
    private ?array $extraFields = [];
    public function getExtraFields(): ?array
    {
        return $this->extraFields ?? [];
    }
    public function setExtraFields(?array $extraFields): self
    {
        $this->extraFields = $extraFields;
        return $this;
    }

    #misc
    public function getExtraFieldsRaw(): ?string
    {
        return $this->extraFields ? json_encode($this->extraFields, JSON_PRETTY_PRINT) : '{}';
    }
    public function setExtraFieldsRaw(?string $extraFieldsRaw): self
    {
        $this->extraFields = json_decode($extraFieldsRaw, true);
        return $this;
    }

    public function getExtraField(string $path, string $type = null): mixed
    {
        return ExtraFieldsHelper::getPath($path, $this->extraFields, $type);
    }

    public function setExtraField(string $path, mixed $value): self
    {
        $this->extraFields = $this->extraFields ?? [];
        ExtraFieldsHelper::setPath($path, $value, $this->extraFields);

        return $this;
    }
}
