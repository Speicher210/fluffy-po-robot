<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot;

use Symfony\Component\Console\Application as BaseApplication;
use Wingu\FluffyPoRobot\Command;

final class Application extends BaseApplication
{
    public const VERSION = '0.6.0';

    public function __construct()
    {
        parent::__construct('Fluffy PO Robot', self::VERSION);
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

        return $commands;
    }
}
