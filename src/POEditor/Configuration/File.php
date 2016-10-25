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
    protected $tag;

    /**
     * @param string $source
     * @param string $translation
     * @param string $tag
     */
    public function __construct(string $source, string $translation, string $tag)
    {
        $this->source = $source;
        $this->translation = $translation;
        $this->tag = $tag;
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
    public function tag(): string
    {
        return $this->tag;
    }
}
