<?php

declare(strict_types=1);

namespace Wingu\FluffyPoRobot\POEditor;

use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use RuntimeException;
use function array_column;
use function array_filter;
use function array_merge;
use function count;
use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\json_encode;
use function Safe\uasort;
use function strtolower;

class Client
{
    private const BASE_URI = 'https://poeditor.com/api/';

    /** @var GuzzleClient */
    private $client;

    /** @var string */
    private $apiToken;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;

        $this->client = new GuzzleClient(
            [
                'base_uri' => self::BASE_URI,
            ]
        );
    }

    /**
     * @return mixed[]
     */
    public function projectDetails(int $idProject) : array
    {
        $response = $this->callAction('view_project', ['id' => $idProject]);

        return $response['item'];
    }

    /**
     * Get the list of projects.
     *
     * @return mixed[]
     */
    public function listProjects() : array
    {
        $response = $this->callAction('list_projects');

        return $response['list'];
    }

    /**
     * Get the list of languages of a project.
     *
     * @return mixed[]
     */
    public function listProjectLanguages(int $idProject) : array
    {
        $projects = $this->callAction('list_languages', ['id' => $idProject]);

        return array_column($projects['list'], 'code');
    }

    /**
     * Sync terms.
     *
     * @param mixed[] $terms
     *
     * @return mixed[]
     */
    public function sync(int $idProject, array $terms) : array
    {
        $response = $this->callAction('sync_terms', ['id' => $idProject, 'data' => json_encode($terms)]);

        return $response['details'];
    }

    /**
     * @param mixed[] $translations
     *
     * @return mixed[]
     */
    public function upload(int $idProject, string $language, array $translations) : array
    {
        if (count($translations) === 0) {
            throw new InvalidArgumentException('You must provide at least one translation.');
        }

        $response = $this->callAction(
            'update_language',
            [
                'id' => $idProject,
                'language' => $language,
                'data' => json_encode($translations),
            ]
        );

        return $response['details'];
    }

    /**
     * Export file.
     *
     * @return mixed[]
     */
    public function export(int $idProject, string $language, string $context) : array
    {
        $response = $this->callAction(
            'export',
            [
                'id' => $idProject,
                'language' => $language,
                'type' => 'json',
                'filters' => 'translated',
            ]
        );

        $content      = file_get_contents($response['item']);
        $translations = [];
        // There can be no translations.
        if ($content !== '') {
            $translations = json_decode(file_get_contents($response['item']), true);

            $translations = array_filter(
                $translations,
                static function ($translation) use ($context) {
                    return $translation['context'] === $context;
                }
            );

            uasort(
                $translations,
                static function ($a, $b) {
                    return strtolower($a['term']) <=> strtolower($b['term']);
                }
            );
        }

        return $translations;
    }

    /**
     * @param mixed[] $parameters
     *
     * @return mixed[]
     */
    private function callAction(string $action, array $parameters = []) : array
    {
        $formParams = [
            'api_token' => $this->apiToken,
            'action' => $action,
        ];

        $response = $this->client->post(
            self::BASE_URI,
            [
                'multipart' => $this->parseFormParams(array_merge($formParams, $parameters)),
            ]
        );

        $apiResponse = json_decode($response->getBody()->getContents(), true);

        if ((int) $apiResponse['response']['code'] !== 200) {
            throw new RuntimeException($apiResponse['response']['message'], (int) $apiResponse['response']['code']);
        }

        return $apiResponse;
    }

    /**
     * @param mixed[] $formParams
     *
     * @return mixed[]
     */
    private function parseFormParams(array $formParams) : array
    {
        $params = [];
        foreach ($formParams as $paramName => $paramContents) {
            $params[] = [
                'name' => $paramName,
                'contents' => $paramContents,
            ];
        }

        return $params;
    }
}
