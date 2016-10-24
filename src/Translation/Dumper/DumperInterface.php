<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

interface DumperInterface extends \Symfony\Component\Translation\Dumper\DumperInterface
{
    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getFileExtension() : string;
}
