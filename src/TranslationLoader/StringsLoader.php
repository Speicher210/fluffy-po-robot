<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\TranslationLoader;

use Symfony\Component\Translation\Loader\CsvFileLoader;

class StringsLoader extends CsvFileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource($resource)
    {
        $this->setCsvControl('=', '"', '\\');

        $content = parent::loadResource($resource);

        $data = array();
        foreach ($content as $key => $value) {
            $data[trim($key)] = substr($value, 0, -1);
        }

        return $data;
    }
}