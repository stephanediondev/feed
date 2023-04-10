<?php

declare(strict_types=1);

namespace App\Command;

use App\Manager\PushManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:notifications', description: 'Send notifications')]
class PushCommand extends Command
{
    private PushManager $pushManager;

    public function __construct(PushManager $pushManager)
    {
        parent::__construct();

        $this->pushManager = $pushManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('UTC');
        $this->pushManager->sendNotifications();

        return Command::SUCCESS;
    }
}
