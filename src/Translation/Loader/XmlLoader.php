<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

/**
 * Xml loader.
 */
class XmlLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource(string $resource): array
    {
        $xml = \simplexml_load_string(\file_get_contents($resource));

        $data = [];

        /** @var \SimpleXMLElement $element */
        foreach ($xml as $element) {
            $name = $this->getAttribute($element, 'name');

            if ($element->getName() === 'string') {
                $data[$name] = $this->cleanTranslation((string)$element[0]);
            } elseif ($element->getName() === 'plurals') {
                $plurals = [];
                foreach ($element->item as $item) {
                    $plurals[] = $this->cleanTranslation((string)$item);
                }
                $data[$name] = $plurals;
            }
        }

        return $data;
    }

    /**
     * @param \SimpleXMLElement $element
     * @param string $attributeName
     * @return string
     */
    private function getAttribute(\SimpleXMLElement $element, string $attributeName): string
    {
        $attributes = $element->attributes();

        return (string)$attributes[$attributeName];
    }

    /**
     * @param string $translation
     * @return string
     */
    private function cleanTranslation(string $translation) : string
    {
        if (0 === \strpos($translation, '"') && \substr($translation, -1) === '"' && \substr($translation, -2) !== '\"') {
            $translation = \substr($translation, 1, -1);
        }

        return \stripcslashes($translation);
    }
}
