<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot;

use KevinGH\Amend;
use Symfony\Component\Console\Application as BaseApplication;
use Wingu\FluffyPoRobot\Command;

class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name = 'Fluffy PO Robot', $version = '@git-version@')
    {
        parent::__construct($name, $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\InitCommand();
        $commands[] = new Command\DownloadCommand();
        $commands[] = new Command\UploadCommand();

        if (('@' . 'git-version@') !== $this->getVersion()) {
            $command = new Amend\Command('update');
            $command->setManifestUri('@manifest_url@');
            $commands[] = $command;
        }

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        $helperSet = parent::getDefaultHelperSet();
        if (('@' . 'git-version@') !== $this->getVersion()) {
            $helperSet->set(new Amend\Helper());
        }

        return $helperSet;
    }
}
