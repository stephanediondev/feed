<?php
declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:setup', description: 'Initialize data')]
class SetupCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private string $kernelProjectDir;

    public function __construct(EntityManagerInterface $entityManager, string $kernelProjectDir)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->kernelProjectDir = $kernelProjectDir;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();

        //add actions
        $file = file_get_contents($this->kernelProjectDir.'/src/DataFixtures/feed.sql');
        if ($file) {
            $sql = 'SELECT COUNT(*) AS total FROM action';
            $count = $connection->fetchAssociative($sql);

            if (true === isset($count['total']) && 0 == $count['total']) {
                $connection->executeQuery($file);
                $output->writeln('<info>Feed data inserted</info>');
            } else {
                $output->writeln('<comment>Feed data already inserted</comment>');
            }
        }

        return Command::SUCCESS;
    }
}
