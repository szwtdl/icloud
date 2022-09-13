<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */
namespace Cloud;

use Cloud\Exceptions\HttpException;
use GuzzleHttp\Client;

class HttpRequest
{
    public Client $client;

    public function __construct(array $options)
    {
        $this->client = new Client(['base_uri' => isset($options['domain']) ? trim($options['domain']) : 'http://localhost:8080']);
    }

    public function get(string $url, array $query): string
    {
        return $this->client->request('GET', $url, $query)->getBody()->getContents();
    }

    public function post(string $url, array $data): string
    {
        try {
            return $this->client->request('POST', $url, $data)->getBody()->getContents();
        } catch (\Exception $exception) {
            throw new HttpException($exception->getMessage());
        }
    }

    public function postJson(string $url, array $data)
    {
        try {
            return json_decode($this->client->request('POST', $url, $data)->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            throw new HttpException($exception->getMessage());
        }
    }
}
