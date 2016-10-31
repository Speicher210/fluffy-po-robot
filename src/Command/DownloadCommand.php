<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;
use Wingu\FluffyPoRobot\POEditor\FormatGuesser;

/**
 * Command to download from POEditor.
 */
class DownloadCommand extends AbstractApiCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('download')
            ->setDescription('Download the translations from POEditor.');
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun()
    {
        $this->io->text('Preparing download ... ');

        foreach ($this->config->files() as $file) {
            $this->downloadFile($file);
        }

        $this->io->success('Downloaded translations. ');
    }

    /**
     * @param File $file
     */
    private function downloadFile(File $file)
    {
        $finder = Finder::create()->in($this->config->basePath())->path($file->source());
        foreach ($finder as $item) {
            $fileDumper = FormatGuesser::fileDumperFromFile($item->getFilename());
            foreach ($this->config->languages() as $originalLanguageCode => $mappedLanguageCode) {
                $translations = $this->apiClient->export(
                    $this->config->projectId(),
                    $originalLanguageCode,
                    $file->tag()
                );

                $filename = $this->buildTranslationFile($file, $item, $mappedLanguageCode);

                $catalog = new MessageCatalogue($originalLanguageCode);
                foreach ($translations as $translation) {
                    $catalog->set($translation['term'], $translation['definition']);
                }

                if (!file_exists($filename)) {
                    $filesystem = new Filesystem();
                    $filesystem->mkdir(array(dirname($filename)));
                }

                $fileDumper->dumpToFile($catalog, 'messages', $filename);

                $this->io->text(sprintf('Updated file: %s', $filename));
            }
        }
    }
}
