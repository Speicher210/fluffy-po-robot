<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor;

use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Wingu\FluffyPoRobot\POEditor\Dumper\CsvDumper;
use Wingu\FluffyPoRobot\POEditor\Dumper\DumperInterface;
use Wingu\FluffyPoRobot\POEditor\Dumper\JsonDumper;
use Wingu\FluffyPoRobot\POEditor\Dumper\MoDumper;
use Wingu\FluffyPoRobot\POEditor\Dumper\PoDumper;
use Wingu\FluffyPoRobot\POEditor\Dumper\StringsDumper;
use Wingu\FluffyPoRobot\POEditor\Dumper\XliffDumper;
use Wingu\FluffyPoRobot\POEditor\Dumper\YamlDumper;
use Wingu\FluffyPoRobot\TranslationLoader\StringsLoader;

/**
 * Try to guess the format from the filename.
 */
class FormatGuesser
{
    public static function formatFromFile($filename) : string
    {
        $extension = pathinfo((string)$filename, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'csv':
            case 'mo':
            case 'po':
            case 'xliff':
            case 'yml':
                return $extension;
            case 'json':
                return 'key_value_json';
            case 'strings':
                return 'apple_strings';
            case 'xml':
                return 'android_strings';
        }

        throw new \RuntimeException('Can not guess format.');
    }

    public static function fileLoaderFromFile($filename) : LoaderInterface
    {
        $format = self::formatFromFile($filename);

        switch ($format) {
            case 'po':
                return new PoFileLoader();
            case 'apple_strings':
                return new StringsLoader();
            case 'key_value_json':
                return new JsonFileLoader();
        }

        throw new \RuntimeException('Can not find a file loader.');
    }

    /**
     * @param string $filename
     * @return DumperInterface
     */
    public static function fileDumperFromFile(string $filename) : DumperInterface
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
                return new YamlDumper();
            case 'apple_strings':
                return new StringsDumper();
            case 'key_value_json':
                return new JsonDumper();
            case 'android_strings':
                throw new \RuntimeException('Not yet implemented.');
        }

        throw new \RuntimeException('Can not find a dumper.');
    }
}