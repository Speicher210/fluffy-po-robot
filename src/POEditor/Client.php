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

    public function projectDetails(int $idProject) : array
    {
        $response = $this->callAction('view_project', array('id' => $idProject));

        return $response['item'];
    }

    /**
     * Get the list of projects.
     *
     * @return array
     */
    public function listProjects() : array
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
    public function listProjectLanguages(int $idProject) : array
    {
        $projects = $this->callAction('list_languages', array('id' => $idProject));

        return array_column($projects['list'], 'code');
    }

    /**
     * Sync terms.
     *
     * @param int $idProject
     * @param array $terms
     * @return array
     */
    public function sync(int $idProject, array $terms) : array
    {
        $terms = \GuzzleHttp\json_encode($terms);

        $response = $this->callAction('sync_terms', array('id' => $idProject, 'data' => $terms));

        return $response['details'];
    }

    /**
     * Upload a file.
     *
     * @param int $idProject
     * @param string $language
     * @param string $file
     * @return array
     */
    public function upload(int $idProject, string $language, string $file) : array
    {
        $response = $this->callAction(
            'upload',
            array(
                'id' => $idProject,
                'language' => $language,
                'overwrite' => '1',
                'updating' => 'definitions',
                'file' => fopen($file, 'r')
            )
        );

        return $response['details'];
    }

    /**
     * Export file.
     *
     * @param int $idProject
     * @param string $language
     * @param string $tag
     * @return array
     */
    public function export(int $idProject, string $language, string $tag) : array
    {
        $response = $this->callAction(
            'export',
            array(
                'id' => $idProject,
                'language' => $language,
                'type' => 'json',
                'filters' => 'translated',
                'tags' => $tag
            )
        );

        $content = file_get_contents($response['item']);
        $translations = array();
        // There can be no translations.
        if ($content !== '') {
            $translations = \GuzzleHttp\json_decode(file_get_contents($response['item']), true);

            uasort(
                $translations,
                function ($a, $b) {
                    return strtolower($a['term']) <=> strtolower($b['term']);
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
    protected function callAction(string $action, array $parameters = array())
    {
        $formParams = array(
            'api_token' => $this->apiToken,
            'action' => $action
        );

        $response = $this->client->post(
            null,
            [
                'multipart' => $this->parseFormParams(array_merge($formParams, $parameters))
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
        $params = array();
        foreach ($formParams as $paramName => $paramContents) {
            $params[] = array(
                'name' => $paramName,
                'contents' => $paramContents
            );
        }

        return $params;
    }
}
