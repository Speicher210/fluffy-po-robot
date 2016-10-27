<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\MoFileDumper;

/**
 * Mo dumper.
 */
class MoDumper extends MoFileDumper implements DumperInterface
{
    use DumperTrait;
}
