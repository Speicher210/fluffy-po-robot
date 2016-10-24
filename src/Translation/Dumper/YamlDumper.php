<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\YamlFileDumper;

/**
 * Yaml dumper.
 */
class YamlDumper extends YamlFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
