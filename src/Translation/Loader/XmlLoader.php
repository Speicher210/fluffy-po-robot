<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use SimpleXMLElement;

use function Safe\file_get_contents;
use function Safe\simplexml_load_string;
use function stripcslashes;
use function strpos;
use function substr;

class XmlLoader extends FileLoader
{
    /**
     * {@inheritDoc}
     */
    protected function loadResource(string $resource): array
    {
        $xml = simplexml_load_string(file_get_contents($resource));

        $data = [];

        foreach ($xml as $element) {
            $name = $this->getAttribute($element, 'name');

            if ($element->getName() === 'string') {
                $data[$name] = $this->cleanTranslation((string) $element[0]);
            } elseif ($element->getName() === 'plurals') {
                $plurals = [];
                foreach ($element->item as $item) {
                    $plurals[] = $this->cleanTranslation((string) $item);
                }

                $data[$name] = $plurals;
            }
        }

        return $data;
    }

    private function getAttribute(SimpleXMLElement $element, string $attributeName): string
    {
        $attributes = $element->attributes();

        return (string) ($attributes[$attributeName] ?? '');
    }

    private function cleanTranslation(string $translation): string
    {
        if (strpos($translation, '"') === 0 && substr($translation, -1) === '"' && substr($translation, -2) !== '\"') {
            $translation = substr($translation, 1, -1);
        }

        return stripcslashes($translation);
    }
}
