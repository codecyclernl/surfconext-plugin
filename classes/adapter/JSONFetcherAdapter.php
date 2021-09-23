<?php namespace Codecycler\SURFconext\Classes\Adapter;

use October\Rain\Network\Http;
use Codecycler\SURFconext\Classes\Contract\JSONGetter;
use Codecycler\SURFconext\Classes\Contract\JSONPoster;

class JSONFetcherAdapter implements JSONGetter, JSONPoster
{
    public function get(string $url, array $params = [], array $options = []): array
    {
        $options = array_merge([
            'query' => $params,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ], $options);

        return $this->request("GET", $url, $options);
    }

    protected function request(string $method, string $url, array $options): array
    {
        $response = Http::make($url, $method, $options);

        if (!$response || !$response->data) {
            return [];
        }

        return json_decode($response->data, true);
    }

    public function post(string $url, array $params = [], $body = null, array $options = []): array
    {
        $options = array_merge([
            'query' => $params,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body,
        ], $options);

        return $this->request("POST", $url, $options);
    }
}
