<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\POEditor\Configuration;

use OutOfBoundsException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

use function array_key_exists;
use function array_map;
use function dirname;
use function Safe\file_get_contents;
use function Safe\realpath;
use function sprintf;

final class Configuration
{
    private string $apiToken;

    private int $projectId;

    /**
     * Relative to the configuration file.
     */
    private string $basePath;

    private string $referenceLanguage;

    /** @var mixed[] */
    private array $languages;

    /** @var File[] */
    private array $files;

    /**
     * @param mixed[] $languages
     * @param File[]  $files
     */
    public function __construct(
        string $apiToken,
        int $projectId,
        string $basePath,
        string $referenceLanguage,
        array $languages,
        array $files
    ) {
        $this->apiToken          = $apiToken;
        $this->projectId         = $projectId;
        $this->basePath          = $basePath;
        $this->referenceLanguage = $referenceLanguage;
        $this->languages         = $languages;
        $this->files             = $files;
    }

    public static function fromYamlFile(string $yamlFilePath): Configuration
    {
        $config = Yaml::parse(file_get_contents($yamlFilePath));

        $basePath = $config['base_path'];

        $filesystem = new Filesystem();
        if (! $filesystem->isAbsolutePath($basePath)) {
            $basePath            = dirname($yamlFilePath) . '/' . $basePath;
            $config['base_path'] = realpath($basePath);
        }

        if ($config['base_path'] === false) {
            throw new RuntimeException(sprintf('Base path "%s" is invalid. Check your config file.', $basePath));
        }

        $config['files'] = array_map(
            static function ($file) {
                return new File($file['source'], $file['translation'], $file['context']);
            },
            $config['files']
        );

        return new static(
            $config['api_token'],
            (int) $config['project_id'],
            $config['base_path'],
            $config['reference_language'],
            $config['languages'],
            $config['files']
        );
    }

    public function toYaml(): string
    {
        $config = [
            'api_token' => $this->apiToken,
            'project_id' => $this->projectId,
            'base_path' => $this->basePath,
            'reference_language' => $this->referenceLanguage,
            'languages' => $this->languages,
            'files' => $this->files,
        ];

        return Yaml::dump($config);
    }

    public function apiToken(): string
    {
        return $this->apiToken;
    }

    public function projectId(): int
    {
        return $this->projectId;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function referenceLanguage(): string
    {
        return $this->referenceLanguage;
    }

    /**
     * @return mixed[]
     */
    public function languages(): array
    {
        return $this->languages;
    }

    public function languageMap(string $language): string
    {
        if (array_key_exists($language, $this->languages)) {
            return $this->languages[$language];
        }

        throw new OutOfBoundsException('Language not defined.');
    }

    /**
     * @return File[]
     */
    public function files(): array
    {
        return $this->files;
    }
}
