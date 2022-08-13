<?php

namespace Cloud\Icloud;

use Cloud\HttpRequest;

class Application
{
    public $request;
    public $options = [];

    public function __construct(array $config)
    {
        $this->request = new HttpRequest($config);
    }

    /**
     * 账号登录
     * @param $username
     * @param $password
     * @return array
     */
    public function login($username, $password): array
    {
        $result = $this->request->postJson('/v2/api/auth', ['json' => ['username' => $username, 'password' => $password]]);
        if (isset($result['ec']) && $result['ec'] == '10000') {
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'status' => $result['status'],
                    'code' => $result['ec'],
                    'msg' => $result['em']
                ]
            ];
        }
        return [
            'code' => 201,
            'msg' => 'fail',
            'data' => $result
        ];
    }

    /**
     * 验证账号
     * @param $username
     * @param $password
     * @param $code
     * @return array
     */
    public function verify($username, $password, $code): array
    {
        $result = $this->request->postJson('v2/api/auth/verify', [
            'json' => [
                'username' => $username,
                'password' => $password,
                'securityCode' => $code
            ]
        ]);
        if (isset($result['ec']) && $result['ec'] === 10001) {
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => [
                    'status' => $result['status'],
                    'code' => $result['ec'],
                    'msg' => $result['em']
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'status' => $result['status'],
                'code' => $result['ec'],
                'msg' => $result['em']
            ]
        ];
    }

    /**
     * 下载数据
     * @param $username
     * @param $password
     * @return array
     */
    public function download($username, $password): array
    {
        $result = $this->request->postJson('v2/api/download', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ]
        ]);
        if (isset($result['status']) && isset($result['ec']) && $result['ec'] == 200) {
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => $result['em']
            ];
        }
        return [
            'code' => 201,
            'msg' => 'fail',
            'data' => $result
        ];
    }

    /**
     * 重置session
     * @param $username
     * @return array
     */
    public function reset($username): array
    {
        $result = $this->request->postJson('v2/api/auth/reset', [
            'json' => ['username' => $username]
        ]);
        if ($result['ec'] === 10001) {
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => [
                    'status' => $result['status'],
                    'code' => $result['ec'],
                    'msg' => $result['em']
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'status' => $result['status'],
                'code' => $result['ec'],
                'msg' => $result['em']
            ]
        ];
    }

    /**
     * 账号设备列表
     * @param $username
     * @return array
     */
    public function account($username): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'ACCOUNT'
                ]
            ]
        ]);
        if (isset($result['totalCount']) && $result['totalCount'] > 0 && isset($result['contents']['devices'])) {
            $list = [];
            foreach ($result['contents']['devices'] as $device) {
                $list[] = $device;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => $list
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => []
        ];
    }

    /**
     * 联系人列表
     * @param $username
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function contact($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'CONTACT',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit
                ]
            ]
        ]);
        if (isset($result['totalCount']) && $result['totalCount'] > 0 && isset($result['contents'])) {
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $result['contents']
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => []
            ]
        ];
    }

    /**
     * 定位列表
     * @param $username
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function location($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'LOCATION',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit
                ]
            ]
        ]);
        if (isset($result['totalCount']) && isset($result['contents'])) {
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $result['contents']
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => []
            ]
        ];
    }

    /**
     * 相册列表
     * @param $username
     * @param int $offset
     * @param int $limit
     * @param $name
     * @return array
     */
    public function albums($username, int $offset = 1, int $limit = 20, $name = 'All Photos'): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'ALBUM_DETAIL',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                    'rid' => $name
                ]
            ]
        ]);
        if (isset($result['totalCount']) && count($result['totalCount']) > 0 && isset($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $list[] = $content;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => []
            ]
        ];
    }

    /**
     * 云盘数据
     * @param $username
     * @param string $name
     * @return array
     */
    public function files($username, string $name = 'root'): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'ICLOUD_DRIVE_DIR',
                    'rid' => $name
                ]
            ]
        ]);
        if (isset($result['contents']) && count($result['contents']) > 0) {
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['contents']['fileCount'],
                    'folder' => [
                        'id' => $result['contents']['docwsid'],
                        'name' => $result['contents']['name'],
                        'parentId' => $result['contents']['parentId'],
                        'type' => strtolower($result['contents']['type']),
                        'size' => $result['contents']['assetQuota'],
                        'create_time' => $result['contents']['dateCreated'],
                        'modified_time' => $result['contents']['dateModified'],
                        'changed_time' => $result['contents']['dateChanged'],
                        'last_open_time' => $result['contents']['lastOpenTime'],
                        'child_num' => $result['contents']['numberOfItems']
                    ],
                    'list' => $result['contents']['items']
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'folder' => [],
                'list' => []
            ]
        ];
    }

    /**
     * 日历列表
     * @param $username
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function calendar($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'CALENDAR',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ]
            ]
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $list[] = $content;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => []
            ]
        ];
    }

    /**
     * 事件列表
     * @param $username
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function events($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'EVENT',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ]
            ]
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $list[] = $content;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => []
            ]
        ];
    }

    /**
     * 提醒事项
     * @param $username
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function reminders($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'REMINDER_SUMMARY',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ]
            ]
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $list[] = $content;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list
                ]
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => []
            ]
        ];
    }
}