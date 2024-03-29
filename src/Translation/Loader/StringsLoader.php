<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Symfony\Component\Translation\Loader\CsvFileLoader;

use function substr;
use function trim;

class StringsLoader extends CsvFileLoader
{
    /**
     * @return string[]
     */
    protected function loadResource(string $resource): array
    {
        $this->setCsvControl('=');

        $content = parent::loadResource($resource);

        $data = [];
        foreach ($content as $key => $value) {
            $data[trim($key)] = substr($value, 0, -1);
        }

        return $data;
    }
}
