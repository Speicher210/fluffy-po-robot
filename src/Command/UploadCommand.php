<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;
use Wingu\FluffyPoRobot\POEditor\Configuration\File;
use Wingu\FluffyPoRobot\POEditor\FormatGuesser;
use function array_keys;
use function count;
use function is_array;
use function Safe\sprintf;

/**
 * Command to upload the translations to POEditor.
 */
class UploadCommand extends AbstractApiCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
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
    protected function doRun() : void
    {
        $terms       = [];
        $sourceFiles = [];
        foreach ($this->config->files() as $file) {
            /** @var File $file */
            $finder = Finder::create()->in($this->config->basePath())->path($file->source());
            if ($finder->count() !== 1) {
                if ($finder->count() === 0) {
                    throw new RuntimeException(sprintf('No source file found for "%s".', $file->source()));
                }

                throw new RuntimeException(sprintf('More than one source file found for "%s"', $file->source()));
            }
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $sourceTranslationFile = $iterator->current();
            $sourceFiles[]         = [
                'sourceTranslationFile' => $sourceTranslationFile,
                'configFile' => $file,
            ];

            $translator = new Translator($this->config->referenceLanguage());
            $fileFormat = FormatGuesser::formatFromFile($sourceTranslationFile);
            $fileLoader = FormatGuesser::fileLoaderFromFile($sourceTranslationFile->getFilename());
            $translator->addLoader($fileFormat, $fileLoader);
            $translator->addResource(
                $fileFormat,
                $sourceTranslationFile,
                $this->config->referenceLanguage(),
                $file->context()
            );

            $messages = $translator->getCatalogue($this->config->referenceLanguage())->all($file->context());
            foreach ($messages as $term => $message) {
                $terms[] = [
                    'term' => $term,
                    'plural' => is_array($message) ? $term : null,
                    'context' => $file->context(),
                ];
            }
        }

        $this->io->section('Synchronizing terms ... ');
        $result = $this->apiClient->sync($this->config->projectId(), $terms);
        $this->io->table(['Parsed', 'Added', 'Updated', 'Deleted'], [$result]);

        if (! $this->input->getOption('include-reference-language')) {
            return;
        }

        $this->uploadTranslations($sourceFiles);
    }

    /**
     * @param mixed[] $sourceFiles
     */
    private function uploadTranslations(array $sourceFiles) : void
    {
        $languages = $this->config->languages();
        // Temporary only upload source because of rate limiting.
        foreach ($languages as $language => $mappedLanguage) {
            $this->io->section(sprintf('Uploading "%s" language from files ...', $language));

            $translator       = new Translator($language);
            $translationFiles = [];
            foreach ($sourceFiles as $sourceFile) {
                $translationFile    = $this->buildTranslationFile(
                    $sourceFile['configFile'],
                    $sourceFile['sourceTranslationFile'],
                    $mappedLanguage
                );
                $translationFiles[] = $translationFile;

                $fileFormat = FormatGuesser::formatFromFile($translationFile);
                $fileLoader = FormatGuesser::fileLoaderFromFile($sourceFile['sourceTranslationFile']->getFilename());

                $translator->addLoader($fileFormat, $fileLoader);
                $translator->addResource($fileFormat, $translationFile, $language, $sourceFile['configFile']->context());
            }
            $this->io->listing($translationFiles);

            $translations = [];
            foreach ($translator->getCatalogue($language)->all() as $context => $catalogTranslations) {
                foreach ($catalogTranslations as $term => $translation) {
                    $translations[] = [
                        'term' => $term,
                        'context' => $context,
                        'translation' => [
                            'content' => (array) $translation,
                            'fuzzy' => 0,
                        ],
                    ];
                }
            }

            if (count($translations) > 0) {
                $response = $this->apiClient->upload(
                    $this->config->projectId(),
                    $language,
                    $translations
                );
                $this->io->table(array_keys($response), [$response]);
            } else {
                $this->io->note(sprintf('No translations found for %s language', $language));
            }
        }
    }
}
