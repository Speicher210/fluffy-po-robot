<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;
use Wingu\FluffyPoRobot\POEditor\FormatGuesser;
use Wingu\FluffyPoRobot\Translation\Dumper\XmlDumper;

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
        $files = $this->config['files'];
        $foundFiles = array();
        foreach ($files as $file) {
            $finder = Finder::create()->in($this->config['base_path'])->path($file['source']);
            foreach ($finder as $item) {
                $foundFiles[$file['tag']] = $item;
            }
        }

        $terms = array();
        foreach ($foundFiles as $tag => $foundFile) {
            $translator = new Translator($this->config['reference_language']);

            $fileFormat = FormatGuesser::formatFromFile($foundFile);
            $fileLoader = FormatGuesser::fileLoaderFromFile($foundFile->getFilename());
            $translator->addLoader($fileFormat, $fileLoader);

            $translator->addResource($fileFormat, $foundFile, $this->config['reference_language'], $tag);
            $messages = $translator->getCatalogue($this->config['reference_language'])->all($tag);
            foreach ($messages as $term => $message) {
                $terms[] = array(
                    'term' => $term,
                    'tags' => array($tag)
                );
            }
        }

        $this->io->text('Synchronizing terms ... ');

        $result = $this->apiClient->sync($this->config['project_id'], $terms);

        $this->io->table(array('Parsed', 'Added', 'Updated', 'Deleted'), array($result));

        if ($this->input->getOption('include-reference-language')) {
            $this->io->text('Uploading reference language translations ... ');

            $translator = new Translator($this->config['reference_language']);
            foreach ($foundFiles as $tag => $foundFile) {
                $fileFormat = FormatGuesser::formatFromFile($foundFile);
                $fileLoader = FormatGuesser::fileLoaderFromFile($foundFile->getFilename());

                $translator->addLoader($fileFormat, $fileLoader);
                $translator->addResource($fileFormat, $foundFile, $this->config['reference_language']);
            }

            $dumper = new XmlDumper();
            $options = array(
                'path' => uniqid(sys_get_temp_dir() . '/', true)
            );
            $dumper->dump($translator->getCatalogue($this->config['reference_language']), $options);
            $file = $options['path'] . '/messages.' . $this->config['reference_language'] . '.xml';

            $response = $this->apiClient->upload(
                $this->config['project_id'],
                $this->config['reference_language'],
                $file
            );

            // Remove the temporary file after upload.
            unlink($file);

            $this->io->table(array_keys($response['definitions']), array($response['definitions']));
        }
    }
}
