<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\XliffFileDumper;

/**
 * Xliff dumper.
 */
class XliffDumper extends XliffFileDumper implements DumperInterface
{
    use DumperTrait;
}
