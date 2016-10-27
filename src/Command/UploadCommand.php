<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;
use Wingu\FluffyPoRobot\POEditor\FormatGuesser;
use Wingu\FluffyPoRobot\Translation\Dumper\PoDumper;

/**
 * Command to upload the translations to POEditor.
 */
class UploadCommand extends AbstractApiCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('upload')
            ->setDescription('Upload the source terms and reference language to POEditor.')
            ->addOption(
                'include-reference-language',
                't',
                InputOption::VALUE_NONE,
                'Flag if the reference language should be uploaded'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun()
    {
        $terms = array();
        $sourceFiles = array();
        foreach ($this->config->files() as $file) {
            /** @var File $file */
            $finder = Finder::create()->in($this->config->basePath())->path($file->source());
            if ($finder->count() !== 1) {
                if ($finder->count() === 0) {
                    throw new \RuntimeException(sprintf('No source file found for "%s".', $file->source()));
                } else {
                    throw new \RuntimeException(sprintf('More than one source file found for "%s"', $file->source()));
                }
            }
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $sourceTranslationFile = $iterator->current();
            $sourceFiles[] = array(
                'sourceTranslationFile' => $sourceTranslationFile,
                'configFile' => $file
            );

            $translator = new Translator($this->config->referenceLanguage());
            $fileFormat = FormatGuesser::formatFromFile($sourceTranslationFile);
            $fileLoader = FormatGuesser::fileLoaderFromFile($sourceTranslationFile->getFilename());
            $translator->addLoader($fileFormat, $fileLoader);
            $translator->addResource($fileFormat, $sourceTranslationFile, $this->config->referenceLanguage(),
                $file->tag());

            $messages = $translator->getCatalogue($this->config->referenceLanguage())->all($file->tag());
            foreach ($messages as $term => $message) {
                $terms[] = array(
                    'term' => $term,
                    'plural' => is_array($message) ? $term : null,
                    'tags' => array($file->tag())
                );
            }
        }

        $this->io->section('Synchronizing terms ... ');
        $result = $this->apiClient->sync($this->config->projectId(), $terms);
        $this->io->table(array('Parsed', 'Added', 'Updated', 'Deleted'), array($result));

        if ($this->input->getOption('include-reference-language')) {
            $this->uploadTranslations($sourceFiles);
        }
    }

    /**
     * Upload the translations.
     *
     * @param array $sourceFiles
     */
    private function uploadTranslations(array $sourceFiles)
    {
//        $languages = $this->config->languages();
        // Temporary only upload source because of rate limiting.
        $languages = array($this->config->referenceLanguage() => $this->config->languageMap($this->config->referenceLanguage()));
        foreach ($languages as $language => $mappedLanguage) {
            $this->io->section(sprintf('Uploading "%s" language from files ...', $language));

            $translator = new Translator($language);
            $translationFiles = array();
            foreach ($sourceFiles as $sourceFile) {
                $translationFile = $this->buildTranslationFile(
                    $sourceFile['configFile'],
                    $sourceFile['sourceTranslationFile'],
                    $mappedLanguage
                );
                $translationFiles[] = $translationFile;

                $fileFormat = FormatGuesser::formatFromFile($translationFile);
                $fileLoader = FormatGuesser::fileLoaderFromFile($sourceFile['sourceTranslationFile']->getFilename());

                $translator->addLoader($fileFormat, $fileLoader);
                $translator->addResource($fileFormat, $translationFile, $language);
            }
            $this->io->listing($translationFiles);

            $dumper = new PoDumper();
            $options = array(
                'path' => uniqid(sys_get_temp_dir() . '/', true)
            );
            $dumper->dump($translator->getCatalogue($language), $options);
            $file = $options['path'] . '/messages.' . $language . '.po';

            $response = $this->apiClient->upload(
                $this->config->projectId(),
                $language,
                $file
            );

            // Remove the temporary file after upload.
            unlink($file);

            $this->io->table(array_keys($response['definitions']), array($response['definitions']));
        }
    }
}
