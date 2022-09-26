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
use Monolog\Logger;

class HttpRequest
{
    public Client $client;

    protected Logger $logger;

    protected array $options = [
        'client_id' => '',
        'client_key' => '',
        'options' => [
            'base_uri' => 'http://localhost:8080',
            'timeout' => 0,
        ],
        'log' => [
            'default' => 'dev', // dev or prod
            'path' => './tmp/icloud.log',
            'level' => 'debug',
        ],
    ];

    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);
        $this->client = new Client($this->options['options']);
        $this->logger = new \Monolog\Logger($this->options['log']['level']);
        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($this->options['log']['path'], $this->options['log']['default'] == 'dev' ? Logger::DEBUG : Logger::INFO));
    }

    public function get(string $url, array $query): string
    {
        return $this->client->request('GET', $url, $query)->getBody()->getContents();
    }

    public function postJson(string $url, array $data): array
    {
        try {
            $result = $this->client->request('POST', $url, $data)->getBody()->getContents();
            $result = json_decode($result, true);
            $this->logger->debug($this->options['log']['level'], [
                'method' => 'post',
                'url' => str_replace('\\', '/', $this->options['options']['base_uri'] . DIRECTORY_SEPARATOR . $url),
                'data' => $data,
                'result' => $result,
            ]);
            return $result;
        } catch (\Exception $exception) {
            return ['code' => 201, 'msg' => $exception->getMessage(), 'data' => []];
        }
    }
}
