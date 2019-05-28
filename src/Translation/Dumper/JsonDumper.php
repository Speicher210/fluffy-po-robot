<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\JsonFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
use const JSON_PRETTY_PRINT;
use function explode;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

/**
 * JSON dumper.
 */
class JsonDumper extends JsonFileDumper implements DumperInterface
{
    use DumperTrait;

    /**
     * {@inheritDoc}
     */
    public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = []) : string
    {
        $json = json_decode(parent::formatCatalogue($messages, $domain, $options), true);

        return json_encode($this->convertToNestedArray($json), JSON_PRETTY_PRINT);
    }

    /**
     * Convert array to nested array.
     *
     * @param mixed[] $json
     *
     * @return mixed[]
     */
    private function convertToNestedArray(array $json) : array
    {
        $result = [];

        foreach ($json as $key => $value) {
            $this->assignArrayByPath($result, $key, $value);
        }

        return $result;
    }

    /**
     * @param mixed[] $arr
     * @param mixed   $value
     */
    private function assignArrayByPath(array &$arr, string $path, $value) : void
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    /**
     * Get the file extension.
     */
    public function getFileExtension() : string
    {
        return $this->getExtension();
    }
}
