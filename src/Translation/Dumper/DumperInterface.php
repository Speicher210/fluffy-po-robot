<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\MessageCatalogue;

interface DumperInterface extends \Symfony\Component\Translation\Dumper\DumperInterface
{
    /**
     * Get the file extension.
     */
    public function getFileExtension() : string;

    /**
     * Dump the translations to a file.
     */
    public function dumpToFile(MessageCatalogue $messages, string $domain, string $filePath) : void;
}
