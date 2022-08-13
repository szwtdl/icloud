<?php

declare(strict_types=1);

/*
 * This file is part of the szwtdl/icloud.
 *
 * (c) pengjian <szpengjian@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @return string
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
