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
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

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

    public function post(string $url, array $data, bool $async = false): string
    {
        try {
            if ($async === true) {
                $promise = $this->client->requestAsync('POST', $url, $data);
                $promise->then(function (ResponseInterface $response) {
                    return $response->getBody()->getContents();
                }, function (RequestException $exception) {
                    return $exception->getMessage();
                });
            } else {
                return $this->client->request('POST', $url, $data)->getBody()->getContents();
            }
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
