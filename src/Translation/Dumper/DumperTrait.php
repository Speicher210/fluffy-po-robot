<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\MessageCatalogue;

trait DumperTrait
{
    /**
     * Dump the translations to a file.
     *
     * @param MessageCatalogue $messages
     * @param string $domain
     * @param string $filePath
     */
    public function dumpToFile(MessageCatalogue $messages, string $domain, string $filePath)
    {
        \file_put_contents($filePath, $this->formatCatalogue($messages, $domain));
    }

    /**
     * Transforms a domain of a message catalogue to its string representation.
     *
     * @param MessageCatalogue $messages
     * @param string $domain
     * @param array $options
     *
     * @return string representation
     */
    abstract public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array());

    /**
     * @return string
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }

    /**
     * @return string
     */
    abstract protected function getExtension();
}
