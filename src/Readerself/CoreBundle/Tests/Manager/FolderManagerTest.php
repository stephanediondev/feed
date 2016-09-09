<?php
namespace Axipi\MCoreBundle\Tests\Manager;

use Readerself\CoreBundle\Entity\Folder;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FolderManagerTest extends KernelTestCase
{
    protected $folderManager;

    protected $memberManager;

    protected function setUp()
    {
        self::bootKernel();

        $this->folderManager = static::$kernel->getContainer()->get('readerself_core_manager_folder');

        $this->memberManager = static::$kernel->getContainer()->get('readerself_core_manager_member');
    }

    public function test()
    {
        $data = $this->folderManager->init();
        $data->setMember($this->memberManager->getOne());
        $data->setTitle('test-unitaire-'.uniqid('', true));

        $id = $this->folderManager->persist($data);

        $test = $this->folderManager->getOne(['id' => $id]);

        $this->assertNotNull($test);
        $this->assertInstanceOf(Folder::class, $test);

        $this->folderManager->remove($data);

        $test = $this->folderManager->getOne(['id' => $id]);

        $this->assertNull($test);
    }
}
