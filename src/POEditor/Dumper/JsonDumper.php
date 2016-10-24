<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Dumper;

use Symfony\Component\Translation\Dumper\JsonFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * JSON POEditor dumper.
 */
class JsonDumper extends JsonFileDumper implements DumperInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array())
    {
        $json = \GuzzleHttp\json_decode(parent::formatCatalogue($messages, $domain, $options), true);

        return \GuzzleHttp\json_encode($this->convertToNestedArray($json), JSON_PRETTY_PRINT);
    }

    /**
     * Convert array to nested array.
     *
     * @param array $json
     * @return array
     */
    private function convertToNestedArray(array $json) : array
    {
        $result = array();

        foreach ($json as $key => $value) {
            $this->assignArrayByPath($result, $key, $value);
        }

        return $result;
    }

    private function assignArrayByPath(&$arr, $path, $value)
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
