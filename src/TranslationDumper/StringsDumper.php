<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\TranslationDumper;

use Symfony\Component\Translation\Dumper\IniFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Strings dumper.
 */
class StringsDumper extends IniFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array())
    {
        $output = '';

        foreach ($messages->all($domain) as $source => $target) {
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

    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
