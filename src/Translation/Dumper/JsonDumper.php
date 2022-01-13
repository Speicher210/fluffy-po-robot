<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Dumper;

use Symfony\Component\Translation\Dumper\JsonFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

use function explode;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

use const JSON_PRETTY_PRINT;

class JsonDumper extends JsonFileDumper implements DumperInterface
{
    use DumperTrait;

    public const FORMAT_FLAT_KEY_VALUE   = 'FLAT_KEY_VALUE';
    public const FORMAT_NESTED_KEY_VALUE = 'NESTED_KEY_VALUE';

    private string $format;

    public function __construct(string $format = self::FORMAT_FLAT_KEY_VALUE)
    {
        $this->format = $format;
    }

    /**
     * @param mixed[] $options
     */
    public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
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
    private function convertToNestedArray(array $json): array
    {
        if ($this->format === self::FORMAT_NESTED_KEY_VALUE) {
            $result = [];

            foreach ($json as $key => $value) {
                $this->assignArrayByPath($result, $key, $value);
            }

            return $result;
        }

        return $json;
    }

    /**
     * @param mixed[] $arr
     */
    private function assignArrayByPath(array &$arr, string $path, mixed $value): void
    {
        $keys = explode('.', $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    public function getFileExtension(): string
    {
        return $this->getExtension();
    }
}
