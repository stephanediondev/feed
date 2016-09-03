<?php
namespace Axipi\FeedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('readerself:crawl')
            ->setDescription('Launch crawler')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('axipi_core_manager_feed')->updateAll();
    }
}
