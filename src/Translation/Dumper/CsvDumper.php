<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\CsvFileDumper;

/**
 * CSV dumper.
 */
class CsvDumper extends CsvFileDumper implements DumperInterface
{
    use DumperTrait;
}
