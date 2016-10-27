<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor\Configuration;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @var int
     */
    protected $projectId;

    /**
     * Relative to the configuration file.
     *
     * @var string
     */
    protected $basePath;

    /**
     * @var string
     */
    protected $referenceLanguage;

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var File[]
     */
    protected $files;

    /**
     * @param string $apiToken
     * @param int $projectId
     * @param string $basePath
     * @param string $referenceLanguage
     * @param array $languages
     * @param array $files
     */
    public function __construct(
        string $apiToken,
        int $projectId,
        string $basePath,
        string $referenceLanguage,
        array $languages,
        array $files
    ) {
        $this->apiToken = $apiToken;
        $this->projectId = $projectId;
        $this->basePath = $basePath;
        $this->referenceLanguage = $referenceLanguage;
        $this->languages = $languages;
        $this->files = $files;
    }

    /**
     * @param string $yamlFilePath
     * @return Configuration
     */
    public static function fromYamlFile(string $yamlFilePath) : Configuration
    {
        $config = Yaml::parse(file_get_contents($yamlFilePath));

        $basePath = $config['base_path'];
        if ($basePath[0] !== '/') {
            $basePath = dirname($yamlFilePath) . '/' . $basePath;
        }

        $config['base_path'] = realpath($basePath);

        $config['files'] = array_map(
            function ($file) {
                return new File($file['source'], $file['translation'], $file['tag']);
            },
            $config['files']
        );

        return new static(
            $config['api_token'],
            (int)$config['project_id'],
            $config['base_path'],
            $config['reference_language'],
            $config['languages'],
            $config['files']
        );
    }

    /**
     * @return string
     */
    public function toYaml()
    {
        $config = array(
            'api_token' => $this->apiToken,
            'project_id' => $this->projectId,
            'base_path' => $this->basePath,
            'reference_language' => $this->referenceLanguage,
            'languages' => $this->languages,
            'files' => $this->files
        );

        return Yaml::dump($config);
    }

    /**
     * @return string
     */
    public function apiToken() : string
    {
        return $this->apiToken;
    }

    /**
     * @return int
     */
    public function projectId() : int
    {
        return $this->projectId;
    }

    /**
     * @return string
     */
    public function basePath() : string
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function referenceLanguage() : string
    {
        return $this->referenceLanguage;
    }

    /**
     * @return array
     */
    public function languages() : array
    {
        return $this->languages;
    }

    /**
     * Get the mapping for a language.
     *
     * @param string $language
     * @return string
     * @throws \OutOfBoundsException If language is not mapped.
     */
    public function languageMap(string $language) : string
    {
        if (array_key_exists($language, $this->languages)) {
            return $this->languages[$language];
        }

        throw new \OutOfBoundsException('Language not defined.');
    }

    /**
     * @return array
     */
    public function files() : array
    {
        return $this->files;
    }
}
