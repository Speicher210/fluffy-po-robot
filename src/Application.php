<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot;

use KevinGH\Amend;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Helper\HelperSet;
use Wingu\FluffyPoRobot\Command;

final class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Fluffy PO Robot', '@git-version@');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands(): array
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
    protected function getDefaultHelperSet(): HelperSet
    {
        $helperSet = parent::getDefaultHelperSet();
        if (('@' . 'git-version@') !== $this->getVersion()) {
            $helperSet->set(new Amend\Helper());
        }

        return $helperSet;
    }
}
