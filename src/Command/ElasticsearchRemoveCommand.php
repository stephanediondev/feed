<?php

declare(strict_types=1);

namespace App\Command;

use App\Manager\SearchManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(name: 'app:elasticsearch:remove', description: 'Remove Elasticsearch records')]
class ElasticsearchRemoveCommand extends Command
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

        $helper = new QuestionHelper();

        $question = new ConfirmationQuestion('Remove Elasticsearch records? (yes) ', false);

        if ($helper->ask($input, $output, $question)) {
            $this->searchManager->remove();
        }

        return Command::SUCCESS;
    }
}
