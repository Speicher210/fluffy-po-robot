<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    protected InputInterface $input;

    protected OutputInterface $output;

    protected SymfonyStyle $io;

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->input  = $input;
        $this->output = $output;

        $this->io = new SymfonyStyle($input, $output);

        return 0;
    }
}
