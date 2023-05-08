<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wingu\FluffyPoRobot\POEditor\Client;
use Wingu\FluffyPoRobot\POEditor\Configuration\Configuration;

use function array_combine;
use function assert;
use function count;
use function ctype_digit;
use function in_array;
use function is_string;
use function pathinfo;
use function Safe\file_put_contents;
use function Safe\getcwd;
use function sprintf;
use function strtolower;

use const PATHINFO_FILENAME;

class InitCommand extends AbstractCommand
{
    protected Client $apiClient;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('init')
            ->setDescription('Initialize the configuration file.')
            ->addOption('api-token', 't', InputOption::VALUE_REQUIRED, 'The API token')
            ->addOption('project', 'p', InputOption::VALUE_REQUIRED, 'The project name of ID')
            ->addOption('base-path', 'd', InputOption::VALUE_REQUIRED, 'The base path for the files.')
            ->addOption(
                'output-file',
                'f',
                InputOption::VALUE_REQUIRED,
                'The file where to dump the config.',
                getcwd() . '/poeditor.yaml',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $apiToken = $this->input->getOption('api-token');
        if (! $apiToken) {
            $apiToken = $this->io->askHidden('API token');
        }

        $this->apiClient = new Client($apiToken);

        $idProject = $this->getProjectID();

        $config = new Configuration(
            $apiToken,
            $idProject,
            $this->getBasePath(),
            $this->getReferenceLanguage($idProject),
            $this->getProjectLanguagesMap($idProject),
            $this->getFiles(),
        );

        $outputFile = $this->input->getOption('output-file');
        assert(is_string($outputFile));
        file_put_contents($outputFile, $config->toYaml());

        return 0;
    }

    /**
     * Get the project ID.
     */
    private function getProjectID(): int
    {
        $projectInput = $this->input->getOption('project');
        if (! $projectInput) {
            $projectInput = $this->io->ask('Project name or ID');
        }

        if (ctype_digit($projectInput)) {
            return (int) $projectInput;
        }

        $projectInput = strtolower($projectInput);

        $projects = $this->apiClient->listProjects();

        foreach ($projects as $project) {
            if (strtolower($project['name']) === $projectInput) {
                return (int) $project['id'];
            }
        }

        throw new InvalidArgumentException('Project not found.');
    }

    /**
     * Get the project languages map.
     *
     * @return mixed[]
     */
    private function getProjectLanguagesMap(int $idProject): array
    {
        $languages = $this->apiClient->listProjectLanguages($idProject);

        $languagesMap = array_combine($languages, $languages);

        if ($this->io->confirm('Do you want to map your languages?') === true) {
            $languagesMap = [];
            foreach ($languages as $language) {
                $languagesMap[$language] = $this->io->ask(sprintf('Enter map for language "%s"', $language), $language);
            }
        }

        return $languagesMap;
    }

    /**
     * Get the files.
     *
     * @return mixed[]
     */
    private function getFiles(): array
    {
        $files    = [];
        $contexts = [];

        while (true) {
            $source = $this->io->ask(
                'Source (leave empty to skip)',
                null,
                static function ($input) use ($files) {
                    $hasSource = count($files) > 0;
                    if ($input === null && $hasSource === false) {
                        throw new InvalidArgumentException('At least one source file is needed.');
                    }

                    return $input ?: false;
                },
            );

            if ($source === false) {
                break;
            }

            $context = $this->io->ask(
                'Context',
                pathinfo($source, PATHINFO_FILENAME),
                static function ($input) use ($contexts) {
                    if (in_array($input, $contexts, true)) {
                        throw new InvalidArgumentException(sprintf('The context "%s" must be unique', $input));
                    }

                    return $input;
                },
            );

            $contexts[] = $context;

            $translation = $this->io->ask('Translation');

            $files[] = [
                'source' => $source,
                'context' => $context,
                'translation' => $translation,
            ];
        }

        return $files;
    }

    private function getBasePath(): string
    {
        $basePath = $this->input->getOption('base-path');
        if (! $basePath) {
            $basePath = $this->io->ask('Base path name (absolute path or relative to the configuration file)', '.');
        }

        return $basePath;
    }

    private function getReferenceLanguage(int $idProject): string
    {
        $details = $this->apiClient->projectDetails($idProject);

        return $details['reference_language'];
    }
}
