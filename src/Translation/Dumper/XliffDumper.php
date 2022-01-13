<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\XliffFileDumper;

class XliffDumper extends XliffFileDumper implements DumperInterface
{
    use DumperTrait;

    public function getFileExtension(): string
    {
        return $this->getExtension();
    }
}
