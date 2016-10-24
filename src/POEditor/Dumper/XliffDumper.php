<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Dumper;

use Symfony\Component\Translation\Dumper\XliffFileDumper;

/**
 * Xliff POEditor dumper.
 */
class XliffDumper extends XliffFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
