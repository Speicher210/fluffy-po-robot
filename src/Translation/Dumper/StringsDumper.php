<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\IniFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
use function is_array;
use function reset;

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
            $target  = is_array($target) ? reset($target) : $target;
            $output .= '"' . $source . '" = "' . $target . "\";\n";
        }

        return $output;
    }

    public function getFileExtension() : string
    {
        return 'strings';
    }
}
