<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:maxmind:update', description: 'Update MaxMind database')]
class MaxmindCommand extends Command
{
    private string $kernelProjectDir;

    private HttpClientInterface $client;

    private bool $maxmindEnabled;

    private string $maxmindAccountId;

    private string $maxmindLicenseKey;

    private const EDITION = 'GeoLite2-City';

    private const DOWNLOAD_URL = 'https://download.maxmind.com/geoip/databases/'.self::EDITION.'/download?suffix=tar.gz';

    public function __construct(string $kernelProjectDir, HttpClientInterface $client, bool $maxmindEnabled, string $maxmindAccountId, string $maxmindLicenseKey)
    {
        parent::__construct();

        $this->kernelProjectDir = $kernelProjectDir;
        $this->client = $client;
        $this->maxmindEnabled = $maxmindEnabled;
        $this->maxmindAccountId = $maxmindAccountId;
        $this->maxmindLicenseKey = $maxmindLicenseKey;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (true === $this->maxmindEnabled && $this->maxmindAccountId && $this->maxmindLicenseKey) {
            $dirMaxmind = $this->kernelProjectDir.'/maxmind';
            if (false === is_dir($dirMaxmind)) {
                mkdir($dirMaxmind);
            }

            try {
                $archivePath = $dirMaxmind.'/'.self::EDITION.'.tar.gz';

                $authBasic = [$this->maxmindAccountId, $this->maxmindLicenseKey];

                $response = $this->client->request('GET', self::DOWNLOAD_URL, ['auth_basic' => $authBasic]);

                if (200 === $response->getStatusCode()) {
                    file_put_contents($archivePath, $response->getContent());

                    $phar = new \PharData($archivePath);
                    $phar->extractTo($dirMaxmind, null, true);

                    $archiveFolder = $dirMaxmind.'/'.self::EDITION;
                    if (is_dir($archiveFolder)) {
                        $directory = new \RecursiveDirectoryIterator($archiveFolder, \FilesystemIterator::SKIP_DOTS);
                        $children = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);
                        foreach ($children as $child) {
                            $child->isDir() ? rmdir($child->getPathname()) : unlink($child->getPathname());
                        }
                        rmdir($archiveFolder);
                    }

                    $directories = new \DirectoryIterator($dirMaxmind);
                    foreach ($directories as $directory) {
                        if ($directory->isDir() && str_starts_with($directory->getFilename(), self::EDITION.'_')) {
                            rename($dirMaxmind.'/'.$directory->getFilename(), $archiveFolder);
                            break;
                        }
                    }
                }
            } catch(\Exception $e) {
            }
        }

        return Command::SUCCESS;
    }
}
