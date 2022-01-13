<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\POEditor;

use RuntimeException;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Wingu\FluffyPoRobot\Translation\Dumper\CsvDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\DumperInterface;
use Wingu\FluffyPoRobot\Translation\Dumper\JsonDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\MoDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\PoDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\StringsDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\XliffDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\XmlDumper;
use Wingu\FluffyPoRobot\Translation\Dumper\YamlDumper;
use Wingu\FluffyPoRobot\Translation\Loader\PoFileLoader;
use Wingu\FluffyPoRobot\Translation\Loader\StringsLoader;
use Wingu\FluffyPoRobot\Translation\Loader\XmlLoader;
use Wingu\FluffyPoRobot\Translation\Loader\YamlFileLoader;

use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * Try to guess the format from the filename.
 */
final class FormatGuesser
{
    public static function formatFromFile(mixed $filename): string
    {
        $extension = pathinfo((string) $filename, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'csv':
            case 'mo':
            case 'po':
            case 'xliff':
            case 'yml':
            case 'yaml':
                return $extension;

            case 'json':
                return 'key_value_json';

            case 'strings':
                return 'apple_strings';

            case 'xml':
                return 'android_strings';
        }

        throw new RuntimeException('Can not guess format.');
    }

    public static function fileLoaderFromFile(mixed $filename): LoaderInterface
    {
        $format = self::formatFromFile($filename);

        switch ($format) {
            case 'po':
                return new PoFileLoader();

            case 'apple_strings':
                return new StringsLoader();

            case 'key_value_json':
                return new JsonFileLoader();

            case 'android_strings':
                return new XmlLoader();

            case 'yml':
            case 'yaml':
                return new YamlFileLoader();
        }

        throw new RuntimeException('Can not find a file loader.');
    }

    public static function fileDumperFromFile(string $filename): DumperInterface
    {
        $format = self::formatFromFile($filename);

        switch ($format) {
            case 'po':
                return new PoDumper();

            case 'csv':
                return new CsvDumper();

            case 'mo':
                return new MoDumper();

            case 'xliff':
                return new XliffDumper();

            case 'yml':
            case 'yaml':
                return new YamlDumper();

            case 'apple_strings':
                return new StringsDumper();

            case 'key_value_json':
                return new JsonDumper();

            case 'android_strings':
                return new XmlDumper();
        }

        throw new RuntimeException('Can not find a dumper.');
    }
}
