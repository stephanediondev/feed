<?php
namespace Readerself\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

abstract class AbstractRepository extends EntityRepository
{
    public function cacheAvailable()
    {
        return function_exists('apcu_clear_cache');
    }
}
