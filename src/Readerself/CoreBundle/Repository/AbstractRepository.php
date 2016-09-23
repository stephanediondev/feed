<?php
namespace Readerself\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

abstract class AbstractRepository extends EntityRepository
{
    public function cacheDriver()
    {
        if(function_exists('apcu_store')) {
            return new \Doctrine\Common\Cache\ApcuCache();
        } else {
            return false;
        }
    }
}
