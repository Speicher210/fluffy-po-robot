<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Command;

use SplFileInfo;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wingu\FluffyPoRobot\POEditor\Client;
use Wingu\FluffyPoRobot\POEditor\Configuration\Configuration;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;

use function assert;
use function file_exists;
use function is_string;
use function Safe\getcwd;
use function sprintf;
use function strtr;

abstract class AbstractApiCommand extends AbstractCommand
{
    protected Client $apiClient;

    protected Configuration $config;

    protected function configure(): void
    {
        $this
            ->addArgument(
                'config-file',
                InputArgument::OPTIONAL,
                'Configuration for the translations.',
                getcwd() . '/poeditor.yaml',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $configFile = $this->input->getArgument('config-file');
        assert(is_string($configFile));
        if (! file_exists($configFile)) {
            $this->io->error(sprintf('Configuration file "%s" not found', $configFile));

            return 1;
        }

        $this->config    = Configuration::fromYamlFile($configFile);
        $this->apiClient = $this->initializeApiClient($this->config->apiToken());

        $this->doRun();

        return 0;
    }

    abstract protected function doRun(): void;

    protected function initializeApiClient(string $apiToken): Client
    {
        return new Client($apiToken);
    }

    protected function buildTranslationFile(File $fileConfiguration, SplFileInfo $sourceFile, string $languageCode): string
    {
        // If language code is for a reference language then we return path to the source.
        if ($this->config->languageMap($this->config->referenceLanguage()) === $languageCode) {
            return $sourceFile->getPathname();
        }

        return strtr(
            $fileConfiguration->translation(),
            [
                '%base_path%' => $this->config->basePath(),
                '%original_path%' => $sourceFile->getPath(),
                '%language_code%' => $languageCode,
                '%file_name%' => $sourceFile->getFilename(),
                '%file_extension%' => $sourceFile->getExtension(),
            ],
        );
    }
}
