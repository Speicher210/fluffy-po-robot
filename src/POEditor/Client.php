<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\POEditor;

use GuzzleHttp\ClientInterface;

class Client
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @param string $apiToken
     */
    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;

        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => 'https://poeditor.com/api/'
            ]
        );
    }

    public function projectDetails(int $idProject): array
    {
        $response = $this->callAction('view_project', ['id' => $idProject]);

        return $response['item'];
    }

    /**
     * Get the list of projects.
     *
     * @return array
     */
    public function listProjects(): array
    {
        $response = $this->callAction('list_projects');

        return $response['list'];
    }

    /**
     * Get the list of languages of a project.
     *
     * @param int $idProject
     * @return array
     */
    public function listProjectLanguages(int $idProject): array
    {
        $projects = $this->callAction('list_languages', ['id' => $idProject]);

        return \array_column($projects['list'], 'code');
    }

    /**
     * Sync terms.
     *
     * @param int $idProject
     * @param array $terms
     * @return array
     */
    public function sync(int $idProject, array $terms): array
    {
        $response = $this->callAction('sync_terms', ['id' => $idProject, 'data' => \GuzzleHttp\json_encode($terms)]);

        return $response['details'];
    }

    /**
     * Upload a file.
     *
     * @param int $idProject
     * @param string $language
     * @param array $translations
     * @return array
     */
    public function upload(int $idProject, string $language, array $translations): array
    {
        if (\count($translations) === 0) {
            throw new \InvalidArgumentException('You must provide at least one translation.');
        }

        $response = $this->callAction(
            'update_language',
            [
                'id' => $idProject,
                'language' => $language,
                'data' => \GuzzleHttp\json_encode($translations)
            ]
        );

        return $response['details'];
    }

    /**
     * Export file.
     *
     * @param int $idProject
     * @param string $language
     * @param string $context
     * @return array
     */
    public function export(int $idProject, string $language, string $context): array
    {
        $response = $this->callAction(
            'export',
            [
                'id' => $idProject,
                'language' => $language,
                'type' => 'json',
                'filters' => 'translated'
            ]
        );

        $content = \file_get_contents($response['item']);
        $translations = [];
        // There can be no translations.
        if ($content !== '') {
            $translations = \GuzzleHttp\json_decode(\file_get_contents($response['item']), true);

            $translations = \array_filter(
                $translations,
                function ($translation) use ($context) {
                    return $translation['context'] === $context;
                }
            );

            \uasort(
                $translations,
                function ($a, $b) {
                    return \strtolower($a['term']) <=> \strtolower($b['term']);
                }
            );
        }

        return $translations;
    }

    /**
     * @param string $action
     * @param array $parameters
     * @return array
     */
    protected function callAction(string $action, array $parameters = []): array
    {
        $formParams = [
            'api_token' => $this->apiToken,
            'action' => $action
        ];

        $response = $this->client->post(
            null,
            [
                'multipart' => $this->parseFormParams(\array_merge($formParams, $parameters))
            ]
        );

        $apiResponse = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        if ((int)$apiResponse['response']['code'] !== 200) {
            throw new \RuntimeException($apiResponse['response']['message'], (int)$apiResponse['response']['code']);
        }

        return $apiResponse;
    }

    /**
     * @param array $formParams
     * @return array
     */
    private function parseFormParams(array $formParams): array
    {
        $params = [];
        foreach ($formParams as $paramName => $paramContents) {
            $params[] = [
                'name' => $paramName,
                'contents' => $paramContents
            ];
        }

        return $params;
    }
}
