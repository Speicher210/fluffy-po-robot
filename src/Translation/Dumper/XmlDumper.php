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
    use DumperTrait;

    /**
     * @var \DOMDocument
     */
    private $domDoc;

    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array())
    {
        $this->domDoc = new \DOMDocument('1.0', 'utf-8');
        $this->domDoc->formatOutput = true;

        $root = $this->domDoc->createElement('resources');
        $rootNode = $this->domDoc->appendChild($root);

        foreach ($messages->all($domain) as $source => $target) {
            if (\is_string($target)) {
                $translationElement = $this->createTranslationElement(
                    'string',
                    'name',
                    $this->escapeTranslation($source)
                );
                $translationNode = $rootNode->appendChild($translationElement);

                $translationNode->appendChild($this->addTranslation($target));
            } elseif (\is_array($target)) {
                $translationElement = $this->createTranslationElement('plurals', 'name', $source);
                $translationNode = $rootNode->appendChild($translationElement);

                foreach ($target as $key => $plural) {
                    $translationElement = $this->createTranslationElement('item', 'quantity', $key);
                    $subNode = $translationNode->appendChild($translationElement);
                    $subNode->appendChild($this->addTranslation($plural));
                }
            }
        }

        return $this->domDoc->saveXML();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'xml';
    }

    /**
     * @param string $name
     * @param string $attributeName
     * @param string $attributeValue
     * @return \DOMElement
     */
    private function createTranslationElement(string $name, string $attributeName, string $attributeValue)
    {
        $translationElement = $this->domDoc->createElement($name);
        $attribute = $this->domDoc->createAttribute($attributeName);
        $attribute->appendChild($this->domDoc->createTextNode($attributeValue));
        $translationElement->appendChild($attribute);

        return $translationElement;
    }

    /**
     * @param string $target
     * @return \DOMCdataSection|\DOMText
     */
    private function addTranslation(string $target)
    {
        // If there are tags in target we create a CDATA section.
        if ($target !== \strip_tags($target)) {
            $translationValue = $this->domDoc->createCDATASection($this->escapeTranslation($target));
        } else {
            $translationValue = $this->domDoc->createTextNode('"' . $this->escapeTranslation($target) . '"');
        }

        return $translationValue;
    }

    /**
     * @param string $translation
     * @return string
     */
    private function escapeTranslation(string $translation) : string
    {
        return \addcslashes($translation, '"\'');
    }
}
