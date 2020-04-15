<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use DOMCdataSection;
use DOMDocument;
use DOMElement;
use DOMText;
use RuntimeException;
use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\MessageCatalogue;
use function addcslashes;
use function is_array;
use function is_string;
use function strip_tags;

class XmlDumper extends FileDumper implements DumperInterface
{
    use DumperTrait;

    /** @var DOMDocument */
    private $domDoc;

    /**
     * @param mixed[] $options
     */
    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []) : string
    {
        $this->domDoc               = new DOMDocument('1.0', 'utf-8');
        $this->domDoc->formatOutput = true;

        $root     = $this->domDoc->createElement('resources');
        $rootNode = $this->domDoc->appendChild($root);

        foreach ($messages->all($domain) as $source => $target) {
            if (is_string($target)) {
                $translationElement = $this->createTranslationElement(
                    'string',
                    'name',
                    $this->escapeTranslation($source)
                );
                $translationNode    = $rootNode->appendChild($translationElement);

                $translationNode->appendChild($this->addTranslation($target));
            } elseif (is_array($target)) {
                $translationElement = $this->createTranslationElement('plurals', 'name', $source);
                $translationNode    = $rootNode->appendChild($translationElement);

                foreach ($target as $key => $plural) {
                    $translationElement = $this->createTranslationElement('item', 'quantity', $key);
                    $subNode            = $translationNode->appendChild($translationElement);
                    $subNode->appendChild($this->addTranslation($plural));
                }
            }
        }

        $xml = $this->domDoc->saveXML();

        if ($xml === false) {
            throw new RuntimeException('Could not generate XML.');
        }

        return $xml;
    }

    private function createTranslationElement(string $name, string $attributeName, string $attributeValue) : DOMElement
    {
        $translationElement = $this->domDoc->createElement($name);
        $attribute          = $this->domDoc->createAttribute($attributeName);
        $attribute->appendChild($this->domDoc->createTextNode($attributeValue));
        $translationElement->appendChild($attribute);

        return $translationElement;
    }

    /**
     * @return DOMCdataSection|DOMText
     */
    private function addTranslation(string $target)
    {
        // If there are tags in target we create a CDATA section.
        if ($target !== strip_tags($target)) {
            $translationValue = $this->domDoc->createCDATASection($this->escapeTranslation($target));
        } else {
            $translationValue = $this->domDoc->createTextNode('"' . $this->escapeTranslation($target) . '"');
        }

        return $translationValue;
    }

    private function escapeTranslation(string $translation) : string
    {
        return addcslashes($translation, '"\'');
    }

    public function getFileExtension() : string
    {
        return $this->getExtension();
    }

    protected function getExtension() : string
    {
        return 'xml';
    }
}
