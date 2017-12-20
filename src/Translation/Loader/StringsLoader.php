<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Symfony\Component\Translation\Loader\CsvFileLoader;

class StringsLoader extends CsvFileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource($resource): array
    {
        $this->setCsvControl('=');

        $content = parent::loadResource($resource);

        $data = [];
        foreach ($content as $key => $value) {
            $data[\trim($key)] = \substr($value, 0, -1);
        }

        return $data;
    }
}
