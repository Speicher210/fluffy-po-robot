<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\IniFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Strings dumper.
 */
class StringsDumper extends IniFileDumper implements DumperInterface
{
    use DumperTrait;

    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = [])
    {
        $output = '';

        foreach ($messages->all($domain) as $source => $target) {
            $target = \is_array($target) ? \reset($target) : $target;
            $output .= '"' . $source . '" = "' . $target . "\";\n";
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'strings';
    }
}
