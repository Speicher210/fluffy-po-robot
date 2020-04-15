<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\POEditor\Configuration;

final class File
{
    private string $source;

    private string $translation;

    private string $context;

    public function __construct(string $source, string $translation, string $context)
    {
        $this->source      = $source;
        $this->translation = $translation;
        $this->context     = $context;
    }

    public function source() : string
    {
        return $this->source;
    }

    public function translation() : string
    {
        return $this->translation;
    }

    public function context() : string
    {
        return $this->context;
    }
}
