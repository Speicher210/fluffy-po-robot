<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\MessageCatalogue;

use function Safe\file_put_contents;

trait DumperTrait
{
    public function dumpToFile(MessageCatalogue $messages, string $domain, string $filePath): void
    {
        file_put_contents($filePath, $this->formatCatalogue($messages, $domain));
    }
}
