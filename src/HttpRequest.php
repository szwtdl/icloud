<?php

namespace Cloud;

use GuzzleHttp\Client;

class HttpRequest
{
    public $client;

    public function __construct(array $options)
    {
        $this->client = new Client(['base_uri' => isset($options['domain']) ? trim($options['domain']) : 'http://localhost:8080']);
    }

    public function get(string $url, array $query)
    {
        return $this->client->request('GET', $url, $query)->getBody()->getContents();
    }

    /**
     * 发起请求
     * @param string $url
     * @param array $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(string $url, array $data)
    {
        return $this->client->request('POST', $url, $data)->getBody()->getContents();
    }

    public function postJson(string $url, array $data)
    {
        return json_decode($this->client->request('POST', $url, $data)->getBody()->getContents(), true);
    }
}