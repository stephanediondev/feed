<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tronovav\GeoIP2Update\Client;

#[AsCommand(name: 'app:maxmind:update', description: 'Update MaxMind databases')]
class MaxmindCommand extends Command
{
    private string $kernelProjectDir;

    private bool $maxmindEnabled;

    private string $maxmindLicenseKey;

    public function __construct(string $kernelProjectDir, bool $maxmindEnabled, string $maxmindLicenseKey)
    {
        parent::__construct();

        $this->kernelProjectDir = $kernelProjectDir;
        $this->maxmindEnabled = $maxmindEnabled;
        $this->maxmindLicenseKey = $maxmindLicenseKey;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (true === $this->maxmindEnabled && $this->maxmindLicenseKey) {
            $client = new Client([
                'license_key' => $this->maxmindLicenseKey,
                'dir' => $this->kernelProjectDir,
                'editions' => ['GeoLite2-City'],
            ]);

            $client->run();
        }

        return Command::SUCCESS;
    }
}
