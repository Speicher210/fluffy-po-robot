<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Translation\Loader;

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageCatalogue;
use function file_exists;
use function Safe\sprintf;
use function stream_is_local;

abstract class FileLoader extends ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages') : MessageCatalogue
    {
        $resource = (string) $resource;

        if (! stream_is_local($resource)) {
            throw new InvalidResourceException(sprintf('This is not a local file "%s".', $resource));
        }

        if (! file_exists($resource)) {
            throw new NotFoundResourceException(sprintf('File "%s" not found.', $resource));
        }

        $messages = $this->loadResource($resource);

        $catalogue = new MessageCatalogue($locale);
        $catalogue->add($messages, $domain);

        return $catalogue;
    }

    /**
     * @return mixed[]
     */
    abstract protected function loadResource(string $resource) : array;
}
