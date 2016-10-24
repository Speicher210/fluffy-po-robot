<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Symfony\Component\Translation\Loader\FileLoader;

class XmlLoader extends FileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource($resource)
    {
        $xml = simplexml_load_file((string)$resource);

        $data = array();

        /** @var \SimpleXMLElement $element */
        foreach ($xml as $element) {
            $attributes = $element->attributes();

            if ($element->getName() === 'string') {
                $data[(string)$attributes['name']] = (string)$element[0];
            } elseif ($element->getName() === 'plurals') {
                $plurals = array();
                foreach ($element->item as $item) {
                    $plurals[] = $item;
                }
                $data[(string)$attributes['name']] = implode('|', $plurals);
            }
        }

        return $data;
    }
}
