<?php

namespace App\Command;

use App\Manager\SearchManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:elasticsearch:init', description: 'Elastic Search init')]
class ElasticsearchInitCommand extends Command
{
    private SearchManager $searchManager;

    public function __construct(SearchManager $searchManager)
    {
        parent::__construct();

        $this->searchManager = $searchManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('UTC');
        $this->searchManager->init();

        return Command::SUCCESS;
    }
}
