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

class Client
{
    private const BASE_URI = 'https://api.poeditor.com/v2/';

    private GuzzleClient $client;

    private string $apiToken;

    public function __construct(string $apiToken)
    {
        $this->apiToken = $apiToken;

        $this->client = new GuzzleClient(
            [
                'base_uri' => self::BASE_URI,
            ],
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function projectDetails(int $idProject): array
    {
        $response = $this->callAction('projects/view', ['id' => $idProject]);

        return $response['result']['project'];
    }

    /**
     * @return mixed[]
     */
    public function listProjects(): array
    {
        $response = $this->callAction('projects/list');

        return $response['result']['projects'];
    }

    /**
     * @return string[]
     */
    public function listProjectLanguages(int $idProject): array
    {
        $projects = $this->callAction('languages/list', ['id' => $idProject]);

        return array_column($projects['result']['languages'], 'code');
    }

    /**
     * @param mixed[] $terms
     *
     * @return array<string,int>
     */
    public function sync(int $idProject, array $terms): array
    {
        $response = $this->callAction('projects/sync', ['id' => $idProject, 'data' => json_encode($terms)]);

        return $response['result']['terms'];
    }

    /**
     * @param mixed[] $translations
     *
     * @return array<string,int>
     */
    public function upload(int $idProject, string $language, array $translations): array
    {
        if (count($translations) === 0) {
            throw new InvalidArgumentException('You must provide at least one translation.');
        }

        $response = $this->callAction(
            'languages/update',
            [
                'id' => $idProject,
                'language' => $language,
                'data' => json_encode($translations),
            ],
        );

        return $response['result']['translations'];
    }

    /**
     * @return mixed[]
     */
    public function export(int $idProject, string $language, string $context): array
    {
        $response = $this->callAction(
            'projects/export',
            [
                'id' => $idProject,
                'language' => $language,
                'type' => 'json',
                'filters' => 'translated',
                'order' => 'terms',
            ],
        );

        $content = file_get_contents($response['result']['url']);
        if ($content === '') {
            return [];
        }

        $translations = json_decode($content, true);

        $translations = array_filter(
            $translations,
            static function ($translation) use ($context) {
                return $translation['context'] === $context;
            },
        );

        return $translations;
    }

    /**
     * @param mixed[] $parameters
     *
     * @return mixed[]
     */
    private function callAction(string $action, array $parameters = []): array
    {
        $formParams = ['api_token' => $this->apiToken];

        $response = $this->client->post(
            self::BASE_URI . $action,
            [
                'multipart' => $this->parseFormParams(array_merge($formParams, $parameters)),
            ],
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
    private function parseFormParams(array $formParams): array
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
