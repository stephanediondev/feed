<?php

declare(strict_types=1);

namespace App\Command;

use App\Kernel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:phpunit', description: 'Show info before PHPUnit')]
class PhpunitCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('PHP version: <info>'.phpversion().'</info>');

        $output->writeln('Symfony version: <info>'.Kernel::VERSION.'</info>');

        $output->writeln('');

        return Command::SUCCESS;
    }
}
