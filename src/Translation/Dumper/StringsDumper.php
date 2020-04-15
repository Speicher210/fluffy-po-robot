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
     * @param mixed[] $options
     */
    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []) : string
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
