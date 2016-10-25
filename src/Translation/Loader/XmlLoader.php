<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageCatalogue;

class XmlLoader extends ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $resource = (string)$resource;

        if (!stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }

        if (!file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }

        $messages = $this->loadResource($resource);

        // empty resource
        if (null === $messages) {
            $messages = array();
        }

        // not an array
        if (!is_array($messages)) {
            throw new InvalidResourceException(sprintf('Unable to load file "%s".', $resource));
        }

        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($messages, $domain);

        return $catalogue;
    }

    /**
     * @param string $resource
     * @return array
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
                    $quantity = $this->getAttribute($item, 'quantity');
                    $plurals[$quantity] = (string)$item;
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
