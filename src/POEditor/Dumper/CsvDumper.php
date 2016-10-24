<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Dumper;

use Symfony\Component\Translation\Dumper\CsvFileDumper;

/**
 * CSV POEditor dumper.
 */
class CsvDumper extends CsvFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
