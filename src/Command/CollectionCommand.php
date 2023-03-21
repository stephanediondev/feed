<?php

namespace App\Command;

use App\Manager\CollectionManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:collection', description: 'Start a new collection')]
class CollectionCommand extends Command
{
    private CollectionManager $collectionManager;

    public function __construct(CollectionManager $collectionManager)
    {
        parent::__construct();

        $this->collectionManager = $collectionManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('UTC');
        $this->collectionManager->start();

        return Command::SUCCESS;
    }
}
