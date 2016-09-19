<?php
namespace Axipi\CoreBundle\DataFixtures\SQL;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class InstallData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $path = __DIR__ . '/../SQL/structure.sql';
        $manager->getConnection()->exec(file_get_contents($path));

        $path = __DIR__ . '/../SQL/data.sql';
        $manager->getConnection()->exec(file_get_contents($path));
    }

    public function getOrder()
    {
        return 0;
    }
}
