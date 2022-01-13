<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\MessageCatalogue;

interface DumperInterface extends \Symfony\Component\Translation\Dumper\DumperInterface
{
    public function getFileExtension(): string;

    public function dumpToFile(MessageCatalogue $messages, string $domain, string $filePath): void;
}
