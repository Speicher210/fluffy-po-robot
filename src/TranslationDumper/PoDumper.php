<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\TranslationDumper;

use Symfony\Component\Translation\Dumper\PoFileDumper;

/**
 * PO dumper.
 */
class PoDumper extends PoFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
