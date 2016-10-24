<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Dumper;

use Symfony\Component\Translation\Dumper\MoFileDumper;

/**
 * Mo POEditor dumper.
 */
class MoDumper extends MoFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
