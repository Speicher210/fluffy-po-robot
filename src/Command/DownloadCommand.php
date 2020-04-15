<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;
use Wingu\FluffyPoRobot\POEditor\FormatGuesser;
use function dirname;
use function file_exists;
use function Safe\sprintf;

class DownloadCommand extends AbstractApiCommand
{
    protected function configure() : void
    {
        parent::configure();

        $this
            ->setName('download')
            ->setDescription('Download the translations from POEditor.');
    }

    protected function doRun() : void
    {
        $this->io->text('Preparing download ... ');

        foreach ($this->config->files() as $file) {
            $this->downloadFile($file);
        }

        $this->io->success('Downloaded translations. ');
    }

    private function downloadFile(File $file) : void
    {
        $finder = Finder::create()->in($this->config->basePath())->path($file->source());
        foreach ($finder as $item) {
            $fileDumper = FormatGuesser::fileDumperFromFile($item->getFilename());
            foreach ($this->config->languages() as $originalLanguageCode => $mappedLanguageCode) {
                $translations = $this->apiClient->export(
                    $this->config->projectId(),
                    $originalLanguageCode,
                    $file->context()
                );

                $filename = $this->buildTranslationFile($file, $item, $mappedLanguageCode);

                $catalog = new MessageCatalogue($originalLanguageCode);
                foreach ($translations as $translation) {
                    $catalog->add([$translation['term'] => $translation['definition']]);
                }

                if (! file_exists($filename)) {
                    $filesystem = new Filesystem();
                    $filesystem->mkdir([dirname($filename)]);
                }

                $fileDumper->dumpToFile($catalog, 'messages', $filename);

                $this->io->text(sprintf('Updated file: %s', $filename));
            }
        }
    }
}
