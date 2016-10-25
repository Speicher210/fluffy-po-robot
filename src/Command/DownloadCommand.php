<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;
use Wingu\FluffyPoRobot\POEditor\FormatGuesser;
use Wingu\FluffyPoRobot\Translation\Loader\PoFileLoader;

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
                $exportFileUrl = $this->apiClient->export(
                    $this->config->projectId(),
                    $originalLanguageCode,
                    $file->tag()
                );

                $filename = strtr(
                    $file->translation(),
                    array(
                        '%base_path%' => $this->config->basePath(),
                        '%original_path%' => $item->getPath(),
                        '%language_code%' => $mappedLanguageCode,
                        '%file_name%' => $item->getFilename(),
                        '%file_extension%' => $item->getExtension()
                    )
                );

                $tmpFile = tempnam(sys_get_temp_dir(), 'fluffy_po_robot');
                file_put_contents($tmpFile, file_get_contents($exportFileUrl));

                $translator = new Translator($originalLanguageCode);
                $translator->addLoader('po', new PoFileLoader());
                $translator->addResource('po', $tmpFile, $originalLanguageCode);

                $options = array(
                    'path' => uniqid(sys_get_temp_dir() . '/', true)
                );
                $fileDumper->dump($translator->getCatalogue(), $options);
                $dumpedFile = $options['path'] . '/messages.' . $originalLanguageCode . '.' . $fileDumper->getFileExtension();
                rename($dumpedFile, $filename);

                $this->io->text(sprintf('Updated file: %s', $filename));
            }
        }
    }
}
