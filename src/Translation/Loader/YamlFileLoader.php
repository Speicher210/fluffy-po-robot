<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Yaml;

/**
 * Yaml loader.
 */
class YamlFileLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource(string $resource): array
    {
        try {
            $yamlParser = new YamlParser();
            $translations = $yamlParser->parse(\file_get_contents($resource), Yaml::DUMP_OBJECT_AS_MAP);
        } catch (ParseException $e) {
            throw new InvalidResourceException(\sprintf('Error parsing YAML, invalid file "%s"', $resource), 0, $e);
        }

        $messages = [];
        foreach ($translations as $key => $message) {
            $messages[$key] = \is_array($message) ? \array_values($message) : $message;
        }

        return $messages;
    }
}
