<?php

namespace App\Trait\Entity;

use App\Helper\ExtraFieldsHelper;
use Doctrine\ORM\Mapping as ORM;

trait ExtraFieldsEntityTrait
{
    /**
     * @var ?array<mixed> $extraFields
     */
    #[ORM\Column(name: "extra_fields", type: "json", nullable: true)]
    private ?array $extraFields = [];

    /**
     * @return array<mixed>
     */
    public function getExtraFields(): ?array
    {
        return $this->extraFields ?? [];
    }

    /**
     * @param array<mixed> $extraFields
     */
    public function setExtraFields(?array $extraFields): self
    {
        $this->extraFields = $extraFields;
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
