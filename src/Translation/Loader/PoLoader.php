<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Geekwright\Po\PoFile;
use Geekwright\Po\PoTokens;

/**
 * Po loader.
 */
class PoLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource(string $resource): array
    {
        $messages = array();

        $poFile = new PoFile();
        $poFile->readPoFile($resource);

        foreach ($poFile->getEntries() as $entry) {
            $key = $entry->getAsString(PoTokens::MESSAGE);
            if (empty($entry->get(PoTokens::PLURAL))) {
                $messages[$key] = $entry->getAsString(PoTokens::TRANSLATED);
            } else {
                $messages[$key] = $entry->getAsStringArray(PoTokens::TRANSLATED);
            }
        }

        return $messages;
    }
}
