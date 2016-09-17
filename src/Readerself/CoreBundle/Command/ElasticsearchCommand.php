<?php
namespace Readerself\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('readerself:elasticsearch')
            ->setDescription('Elastic Search indexation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('readerself_core_manager_elasticsearch')->start();
    }
}
