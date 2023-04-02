<?php

declare(strict_types=1);

namespace App\Command;

use App\Manager\SearchManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:elasticsearch:check', description: 'Check Elastic Search records')]
class ElasticsearchCheckCommand extends Command
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
            $path = '/_refresh';
            $this->searchManager->query('GET', $path);

            $types = ['author', 'category', 'feed', 'item'];

            $rows = [];
            foreach ($types as $type) {
                $path = '/'.$this->searchManager->getIndex().'_'.$type.'/_stats';
                $result = $this->searchManager->query('GET', $path);
                if (false === isset($result->error)) {
                    $rows[] = [$type, $result['_all']['primaries']['docs']['count']];
                }
            }

            $table = new Table($output);
            $table->setHeaders(['index', 'count'])->setRows($rows);
            $table->render();
        }

        return Command::SUCCESS;
    }
}
