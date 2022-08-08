<?php

namespace Szwtdl\Icloud;

use GuzzleHttp\Client;
use Szwtdl\Icloud\Exceptions\HttpException;

class Icloud
{
    protected $client_id;
    protected $client_key;
    protected $client;

    public function __construct($client_id, $client_key, $options = [])
    {
        $this->client_id = $client_id;
        $this->client_key = $client_key;
        $this->client = new Client([
            'base_uri' => isset($options['host']) ? trim($options['host']) : 'http://localhost:8080',
            'timeout' => isset($options['timeout']) ? trim($options['timeout']) : 0,
        ]);
    }

    public function login($email, $pass): array
    {
        try {
            $result = $this->client->request('POST', '/v2/api/auth', [
                'auth' => [$this->client_id, $this->client_key, 'digest'],
                'json' => [
                    'username' => $email,
                    'password' => $pass
                ]
            ])->getBody()->getContents();
            $result = json_decode($result, true);
            if (isset($result['ec']) && $result['ec'] == '10000') {
                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'data' => $result
                ];
            }
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => $result
            ];
        } catch (\Exception $exception) {
            throw new HttpException($exception->getCode(), $exception->getMessage(), $exception);
        }
    }

    public function verify($email, $pass)
    {

    }

    public function reset($email, $pass)
    {

    }

    public function down($email, $pass)
    {

    }

    public function account($email): array
    {
        try {
            $result = $this->client->request('POST', '/v2/api/database/retrieve', [
                'auth' => [$this->client_id, $this->client_key, 'digest'],
                'json' => [
                    'username' => $email,
                    'params' => [
                        'category' => 'ACCOUNT'
                    ]
                ]
            ])->getBody()->getContents();
            $result = json_decode($result, true);
            if (isset($result['totalCount']) && isset($result['contents']) && count($result['contents']) > 0) {
                $device = [];
                foreach ($result['contents'] as $content) {
                    $device[] = $content[0];
                }
                return [
                    'code' => 200,
                    'msg' => 'ok',
                    'data' => $device
                ];
            }
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => []
            ];
        } catch (\Exception $exception) {
            throw new HttpException($exception->getCode(), $exception->getMessage(), $exception);
        }
    }

    public function contact($email, $offset = 1, $limit = 20): array
    {
        try {
            $result = $this->client->request('POST', '/v2/api/database/retrieve', [
                'auth' => [$this->client_id, $this->client_key, 'digest'],
                'json' => [
                    'username' => $email,
                    'params' => [
                        'category' => 'CONTACT',
                        'offset' => ($offset - 1) * $limit,
                        'limit' => $limit
                    ]
                ]
            ])->getBody()->getContents();
            return json_decode($result, true);
        } catch (\Exception $exception) {
            throw new HttpException($exception->getCode(), $exception->getMessage(), $exception);
        }
    }
}