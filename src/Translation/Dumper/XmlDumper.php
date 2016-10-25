<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Gettext\Languages\Language;
use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * XML dumper.
 */
class XmlDumper extends FileDumper implements DumperInterface
{
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

        $languageRules = Language::getById($messages->getLocale());

        foreach ($messages->all($domain) as $source => $target) {
            if (is_string($target)) {
                $translationElement = $this->createTranslationElement('string', 'name', $source);
                $translationNode = $rootNode->appendChild($translationElement);

                $translationNode->appendChild($this->addTranslation($target));
            } elseif (is_array($target)) {
                $translationElement = $this->createTranslationElement('plurals', 'name', $source);
                $translationNode = $rootNode->appendChild($translationElement);

                foreach ($target as $key => $plural) {
                    $quantity = $languageRules->categories[$key]->id;
                    $translationElement = $this->createTranslationElement('item', 'quantity', $quantity);
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
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
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
        if ($target !== strip_tags($target)) {
            $translationValue = $this->domDoc->createCDATASection($target);
        } else {
            $translationValue = $this->domDoc->createTextNode('"' . addcslashes($target, '"') .'"');
        }

        return $translationValue;
    }
}
