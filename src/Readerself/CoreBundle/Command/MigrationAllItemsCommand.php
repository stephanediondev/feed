<?php
namespace Readerself\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationAllItemsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('readerself:migration:all_items')
            ->setDescription('Migration from old version: get all items')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('readerself_core_manager_migration')->start('all_items');
    }
}
