<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Configuration;

class File
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $translation;

    /**
     * @var string
     */
    protected $context;

    /**
     * @param string $source
     * @param string $translation
     * @param string $context
     */
    public function __construct(string $source, string $translation, string $context)
    {
        $this->source = $source;
        $this->translation = $translation;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function source(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function translation(): string
    {
        return $this->translation;
    }

    /**
     * @return string
     */
    public function context(): string
    {
        return $this->context;
    }
}
