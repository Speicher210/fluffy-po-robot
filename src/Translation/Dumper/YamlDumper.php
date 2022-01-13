<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\YamlFileDumper;

class YamlDumper extends YamlFileDumper implements DumperInterface
{
    use DumperTrait;

    public function getFileExtension(): string
    {
        return $this->getExtension();
    }
}
