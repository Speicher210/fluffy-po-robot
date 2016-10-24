<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\TranslationDumper;

use Symfony\Component\Translation\Dumper\CsvFileDumper;

/**
 * CSV dumper.
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
