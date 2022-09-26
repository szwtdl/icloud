<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */
namespace Cloud;

use GuzzleHttp\Client;

class HttpRequest
{
    public Client $client;

    protected array $options = [
        'client_id' => '',
        'client_key' => '',
        'options' => [
            'base_uri' => 'http://localhost:8080',
            'timeout' => 0,
        ],
    ];

    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
        $this->client = new Client($this->options['options']);
    }

    public function get(string $url, array $query): string
    {
        return $this->client->request('GET', $url, $query)->getBody()->getContents();
    }

    public function postJson(string $url, array $data): array
    {
        try {
            $result = $this->client->request('POST', $url, $data)->getBody()->getContents();
            return json_decode($result, true);
        } catch (\Exception $exception) {
            return ['code' => 201, 'msg' => $exception->getMessage(), 'data' => []];
        }
    }
}
