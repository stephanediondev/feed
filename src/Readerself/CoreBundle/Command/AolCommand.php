<?php
namespace Readerself\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AolCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('readerself:aol')
            ->setDescription('Import Aol feeds')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        date_default_timezone_set('UTC');
        $this->getContainer()->get('readerself_core_manager_aol')->start($this->getContainer()->getParameter('kernel.root_dir'));
    }
}
