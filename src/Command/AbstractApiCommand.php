<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wingu\FluffyPoRobot\POEditor\Client;
use Wingu\FluffyPoRobot\POEditor\Configuration\Configuration;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;

abstract class AbstractApiCommand extends AbstractCommand
{
    /**
     * @var Client
     */
    protected $apiClient;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'config-file',
                InputArgument::OPTIONAL,
                'Configuration for the translations.',
                getcwd() . '/poeditor.yml'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $configFile = $this->input->getArgument('config-file');
        if (!file_exists($configFile)) {
            $this->io->error(sprintf('Configuration file "%s" not found', $configFile));

            return 0;
        }
        $this->config = Configuration::fromYamlFile($configFile);
        $this->apiClient = $this->initializeApiClient($this->config->apiToken());

        return $this->doRun();
    }

    /**
     * @return integer
     */
    abstract protected function doRun();

    /**
     * @param string $apiToken
     * @return Client
     */
    protected function initializeApiClient(string $apiToken) : Client
    {
        return new Client($apiToken);
    }

    /**
     * @param File $fileConfiguration
     * @param \SplFileInfo $sourceFile
     * @param string $languageCode
     * @return string
     */
    protected function buildTranslationFile(File $fileConfiguration, \SplFileInfo $sourceFile, string $languageCode) : string
    {
        // If language code is for a reference language then we return path to the source.
        if ($this->config->languageMap($this->config->referenceLanguage()) === $languageCode) {
            return $sourceFile->getPathname();
        }

        return strtr(
            $fileConfiguration->translation(),
            array(
                '%base_path%' => $this->config->basePath(),
                '%original_path%' => $sourceFile->getPath(),
                '%language_code%' => $languageCode,
                '%file_name%' => $sourceFile->getFilename(),
                '%file_extension%' => $sourceFile->getExtension()
            )
        );
    }
}
