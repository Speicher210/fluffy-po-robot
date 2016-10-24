<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * XML dumper.
 */
class XmlDumper extends FileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array())
    {
        $domDoc = new \DOMDocument('1.0', 'utf-8');

        $root = $domDoc->createElement('resources');
        $rootNode = $domDoc->appendChild($root);

        foreach ($messages->all($domain) as $source => $target) {
            $subElt = $domDoc->createElement('string');
            $attr = $domDoc->createAttribute('name');
            $attrVal = $domDoc->createTextNode($source);
            $attr->appendChild($attrVal);
            $subElt->appendChild($attr);
            $subNode = $rootNode->appendChild($subElt);

            // If there are tags in target we create a CDATA section.
            if ($target !== strip_tags($target)) {
                $textNode = $domDoc->createCDATASection($target);
            } else {
                $textNode = $domDoc->createTextNode($target);
            }

            $subNode->appendChild($textNode);
        }

        return $domDoc->saveXML();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'xml';
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
