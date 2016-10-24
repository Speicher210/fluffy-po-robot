<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Dumper;

use Symfony\Component\Translation\Dumper\YamlFileDumper;

/**
 * Yaml POEditor dumper.
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
