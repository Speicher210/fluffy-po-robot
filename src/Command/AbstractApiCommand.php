<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Wingu\FluffyPoRobot\POEditor\Client;

abstract class AbstractApiCommand extends AbstractCommand
{
    /**
     * @var Client
     */
    protected $apiClient;

    /**
     * @var array
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

        $this->config = Yaml::parse(file_get_contents($this->input->getArgument('config-file')));
        $this->config['base_path'] = realpath($this->config['base_path']);

        $this->apiClient = new Client($this->config['api_token']);

        return $this->doRun();
    }

    /**
     * @return integer
     */
    abstract protected function doRun();
}
