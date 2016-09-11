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

    public function testId()
    {
        $title = 'test-'.uniqid('', true);
        $folder = $this->memberManager->folderManager->init();
        $folder->setMember($this->memberManager->getOne());
        $folder->setTitle($title);

        $folder_id = $this->memberManager->folderManager->persist($folder);

        $test = $this->memberManager->folderManager->getOne(['id' => $folder_id]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Folder::class, $test);

        $this->memberManager->folderManager->remove($folder);

        $test = $this->memberManager->folderManager->getOne(['id' => $folder_id]);
        $this->assertNull($test);
    }

    public function testTitle()
    {
        $title = 'test-'.uniqid('', true);
        $folder = $this->memberManager->folderManager->init();
        $folder->setMember($this->memberManager->getOne());
        $folder->setTitle($title);

        $folder_id = $this->memberManager->folderManager->persist($folder);

        $test = $this->memberManager->folderManager->getOne(['title' => $title]);
        $this->assertNotNull($test);
        $this->assertInstanceOf(Folder::class, $test);
        $this->assertEquals($title, $test->getTitle());

        $this->memberManager->folderManager->remove($folder);

        $test = $this->memberManager->folderManager->getOne(['title' => $title]);
        $this->assertNull($test);
    }
}
