<?php

declare(strict_types=1);

namespace App\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractManager
{
    protected EventDispatcherInterface $eventDispatcher;

    #[Required]
    public function setRequired(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function clearCache(): void
    {
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
    }
}
