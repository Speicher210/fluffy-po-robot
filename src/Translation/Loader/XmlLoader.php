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
        $xml = simplexml_load_file($resource);

        $data = array();

        /** @var \SimpleXMLElement $element */
        foreach ($xml as $element) {
            $name = $this->getAttribute($element, 'name');

            if ($element->getName() === 'string') {
                $data[$name] = (string)$element[0];
            } elseif ($element->getName() === 'plurals') {
                $plurals = array();
                foreach ($element->item as $item) {
                    $plurals[] = (string)$item;
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
    private function getAttribute(\SimpleXMLElement $element, string $attributeName)
    {
        $attributes = $element->attributes();

        return (string)$attributes[$attributeName];
    }
}
