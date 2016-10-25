<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wingu\FluffyPoRobot\POEditor\Client;
use Wingu\FluffyPoRobot\POEditor\Configuration\Configuration;

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

        $this->config = Configuration::fromYamlFile($this->input->getArgument('config-file'));
        $this->apiClient = new Client($this->config->apiToken());

        return $this->doRun();
    }

    /**
     * @return integer
     */
    abstract protected function doRun();
}
