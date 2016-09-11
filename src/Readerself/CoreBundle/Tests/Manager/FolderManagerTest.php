<?php
namespace Readerself\CoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Folder;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FolderManagerTest extends KernelTestCase
{
    protected $memberManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->memberManager = static::$kernel->getContainer()->get('readerself_core_manager_member');
    }

    public function test()
    {
        $data = $this->memberManager->folderManager->init();
        $data->setMember($this->memberManager->getOne());
        $data->setTitle('test-'.uniqid('', true));

        $id = $this->memberManager->folderManager->persist($data);

        $test = $this->memberManager->folderManager->getOne(['id' => $id]);

        $this->assertNotNull($test);
        $this->assertInstanceOf(Folder::class, $test);

        $this->memberManager->folderManager->remove($data);

        $test = $this->memberManager->folderManager->getOne(['id' => $id]);

        $this->assertNull($test);
    }
}
