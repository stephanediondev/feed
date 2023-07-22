<?php

declare(strict_types=1);

namespace App\Command;

use App\Manager\SearchManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:elasticsearch:status', description: 'Check Elasticsearch status')]
class ElasticsearchStatusCommand extends Command
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

        if ($this->searchManager->getEnabled()) {
            $path = '/';
            $result = $this->searchManager->query('GET', $path);
            if (true === is_array($result)) {
                if (true === isset($result['error'])) {
                    $output->writeln('<error>'.print_r($result, true).'</error>');
                } else {
                    $output->writeln('<info>'.print_r($result, true).'</info>');
                }
            } else {
                $output->writeln('<error>'.$result.'</error>');
            }
        } else {
            $output->writeln('<comment>Elasticsearch not enabled</comment>');
        }

        return Command::SUCCESS;
    }
}
