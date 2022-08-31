<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */

namespace Cloud\Icloud;

use Carbon\Carbon;
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
     * 账号登录.
     * @param $username
     * @param $password
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
                    'msg' => $result['em'],
                ],
            ];
        }
        return [
            'code' => 201,
            'msg' => 'fail',
            'data' => $result,
        ];
    }

    /**
     * 验证账号.
     * @param $username
     * @param $password
     * @param $code
     */
    public function verify($username, $password, $code): array
    {
        $result = $this->request->postJson('v2/api/auth/verify', [
            'json' => [
                'username' => $username,
                'password' => $password,
                'securityCode' => $code,
            ],
        ]);
        if (isset($result['ec']) && $result['ec'] === 10001) {
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => [
                    'status' => $result['status'],
                    'code' => $result['ec'],
                    'msg' => $result['em'],
                ],
            ];
        }
        if (isset($result['ec']) && $result['ec'] == 10004) {
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => [
                    'status' => $result['status'],
                    'code' => $result['ec'],
                    'msg' => $result['em'],
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'status' => $result['status'],
                'code' => $result['ec'],
                'msg' => $result['em'],
            ],
        ];
    }

    /**
     * 下载数据.
     * @param $username
     * @param $password
     */
    public function download($username, $password): array
    {
        $result = $this->request->postJson('v2/api/download', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);
        if (isset($result['status'], $result['ec']) && $result['ec'] == 200) {
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => $result['em'],
            ];
        }
        return [
            'code' => 201,
            'msg' => 'fail',
            'data' => $result,
        ];
    }

    /**
     * 重置session.
     * @param $username
     */
    public function reset($username): array
    {
        $result = $this->request->post('v2/api/auth/reset', [
            'json' => ['username' => $username],
        ]);
        if ($result != 'Task done.') {
            return [
                'code' => 201,
                'msg' => 'fail',
                'data' => $result,
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => '',
        ];
    }

    /**
     * 账号设备列表.
     * @param $username
     */
    public function account($username): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'ACCOUNT',
                ],
            ],
        ]);
        if (isset($result['totalCount']) && $result['totalCount'] > 0 && isset($result['contents']['devices'])) {
            $list = [];
            foreach ($result['contents']['devices'] as $device) {
                $list[] = $device;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => $list,
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [],
        ];
    }

    /**
     * 联系人列表.
     * @param $username
     */
    public function contact($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'CONTACT',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ],
            ],
        ]);
        if (isset($result['totalCount']) && $result['totalCount'] > 0 && isset($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $key => $item) {
                if ($item['phones'] === null) {
                    continue;
                }
                //通话记录
                if (empty($item['firstName']) && empty($item['lastName']) && is_array($item['phones'][0])) {
                    $item['nickName'] = $item['companyName'];
                    $item['firstName'] = $item['companyName'];
                }
                if (!empty($item['phones']) && empty($item['firstName']) && empty($item['lastName']) && empty($item['companyName'])) {
                    $tmp = $item['phones'][0];
                    $item['firstName'] = $tmp['field'];
                    $item['companyName'] = $tmp['field'];
                }
                $list[] = [
                    'contactId' => $item['contactId'],
                    'nickName' => $item['nickName'],
                    'firstName' => $item['firstName'],
                    'lastName' => $item['lastName'],
                    'notes' => $item['notes'],
                    'companyName' => $item['companyName'],
                    'jobTitle' => $item['jobTitle'],
                    'whitelisted' => $item['whitelisted'],
                    'isGuardianApproved' => $item['isGuardianApproved'],
                    'phones' => $item['phones'],
                    'phone' => $item['phones'] == null ? [] : $item['phones'][0],
                    'urls' => $item['urls'],
                    'emailAddresses' => $item['emailAddresses'],
                    'email' => $item['emailAddresses'] == null ? '' : $item['emailAddresses'][0],
                    'streetAddresses' => $item['streetAddresses'],
                    'streetAddresse' => $item['streetAddresses'] == null ? '' : $item['streetAddresses'][0],
                    'profiles' => $item['profiles'] == null ? [] : $item['profiles'],
                    'profile' => $item['profiles'] == null ? '' : 2,
                ];
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * 定位列表.
     * @param $username
     */
    public function location($username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'LOCATION',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ],
            ],
        ]);
        if (isset($result['totalCount']) && $result['totalCount'] > 0) {
            $list = [];
            foreach ($result['contents'] as $content) {
                if ($content['latitude'] == 0 || $content['longitude'] == 0) {
                    continue;
                }
                $list[] = [
                    'timeStamp' => empty($content['timeStamp']) ? '' : date('Y-m-d H:i:s', intval($content['timeStamp'] / 1000)),
                    'altitude' => $content['altitude'],
                    'latitude' => $content['latitude'],
                    'longitude' => $content['longitude'],
                    'horizontalAccuracy' => $content['horizontalAccuracy'],
                    'verticalAccuracy' => $content['verticalAccuracy'],
                    'type' => $content['positionType'] == 'Wifi' ? 'wifi' : 'image',
                    'sourceRef' => $content['positionType'] == 'Wifi' ? $content['sourceRef'] : strtolower($content['sourceRef']),
                ];
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * 相册列表.
     * @param $username
     * @param $name
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
                    'rid' => $name,
                ],
            ],
        ]);
        if (isset($result['totalCount']) && $result['totalCount'] > 0 && isset($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $list[] = [
                    'id' => md5($content['id']),
                    'filename' => str_replace("./", '', getEscape($content['filename'])),
                    'created' => (new Carbon())->create($content['created'])->toDateTimeString(),
                    'type' => empty($content['heif2jpg']['type']) ? strtolower($content['original']['type']) : strtolower($content['heif2jpg']['type']),
                    'original' => array_merge($content['original'], [
                        'url' => getEscape($content['original']['url'])
                    ]),
                    'medium' => array_merge($content['medium'], [
                        'url' => getEscape($content['medium']['url'])
                    ]),
                    'thumb' => array_merge($content['thumb'], [
                        'url' => getEscape($content['thumb']['url'])
                    ]),
                    'heif2jpg' => array_merge($content['heif2jpg'], [
                        'url' => getEscape($content['heif2jpg']['url'])
                    ])
                ];
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * 云盘数据.
     * @param $username
     */
    public function files($username, string $name = 'root'): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'ICLOUD_DRIVE_DIR',
                    'rid' => $name,
                ],
            ],
        ]);
        if (isset($result['contents']) && count($result['contents']) > 0) {
            $data = [
                'total' => empty($result['contents']['items']) ? 0 : count($result['contents']['items']),
                'list' => [],
                'folder' => [
                    'id' => $result['contents']['docwsid'],
                    'name' => $result['contents']['name'],
                    'parentId' => str_replace('FOLDER::com.apple.CloudDocs::', '', $result['contents']['parentId']),
                    'type' => strtolower($result['contents']['type']),
                    'size' => empty($result['contents']['assetQuota']) ? '0KB' : format_size($result['contents']['assetQuota']),
                    'create_time' => $result['contents']['dateCreated'],
                    'modified_time' => $result['contents']['dateModified'],
                    'changed_time' => $result['contents']['dateChanged'],
                    'last_open_time' => $result['contents']['lastOpenTime'],
                    'child_num' => $result['contents']['numberOfItems'],
                ]
            ];
            if (isset($result['contents']['items']) && count($result['contents']['items']) > 0) {
                foreach ($result['contents']['items'] as $key => $child) {
                    if ($child['type'] == 'APP_LIBRARY') {
                        continue;
                    }
                    if ($child['type'] == 'FILE' && $child['url'] == '') {
                        continue;
                    }
                    if ($child['type'] == 'FOLDER') {
                        $data['list'][$key] = [
                            'id' => $child['docwsid'],
                            'name' => urldecode($child['name']),
                            'parentId' => str_replace('FOLDER::com.apple.CloudDocs::', '', $child['parentId']),
                            'type' => strtolower($child['type']),
                            'icon' => 'folder',
                            'size' => empty($child['assetQuota']) ? '0KB' : format_size($child['assetQuota']),
                            'create_time' => (new Carbon())->create($child['dateCreated'])->toDateTimeString(),
                            'modify_time' => (new Carbon())->create($child['dateModified'])->toDateTimeString(),
                            'update_time' => (new Carbon())->create($child['dateChanged'])->toDateTimeString(),
                            'last_time' => (new Carbon())->create($child['lastOpenTime'])->toDateTimeString(),
                            'url' => empty($child['url']) ? '' : $child['url']
                        ];
                    } else {
                        $data['list'][$key] = [
                            'id' => $child['docwsid'],
                            'name' => urldecode($child['name']),
                            'parentId' => str_replace('FOLDER::com.apple.CloudDocs::', '', $child['parentId']),
                            'type' => strtolower($child['type']),
                            'icon' => empty($child['extension']) ? 'rar' : getExtension($child['extension']),
                            'create_time' => (new Carbon())->create($child['dateCreated'])->toDateTimeString(),
                            'modify_time' => (new Carbon())->create($child['dateModified'])->toDateTimeString(),
                            'update_time' => (new Carbon())->create($child['dateChanged'])->toDateTimeString(),
                            'last_time' => (new Carbon())->create($child['lastOpenTime'])->toDateTimeString(),
                            'url' => empty($child['url']) ? '' : $child['url'],
                            'size' => empty($child['size']) ? '0KB' : format_size($child['size']),
                            'extension' => empty($child['extension']) ? '' : trim($child['extension']),
                        ];
                    }
                }
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => $data
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'folder' => [],
                'list' => [],
            ],
        ];
    }

    /**
     * 日历列表.
     * @param $username
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
                ],
            ],
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $list[] = [
                    'guid' => $content['guid'],
                    'title' => $content['title'],
                    'etag' => $content['etag'],
                    'shareTitle' => $content['shareTitle'],
                    'type' => $content['objectType'],
                    'color' => $content['color'],
                    'symbolicColor' => $content['symbolicColor'],
                    'enabled' => $content['enabled'],
                    'url' => empty($content['prePublishedUrl']) ? '' : $content['prePublishedUrl'],
                    'create_time' => (new Carbon())->create($content['createdDate'][1] . '-' . $content['createdDate'][2] . '-' . $content['createdDate'][3] . ' ' . $content['createdDate'][4] . ':' . $content['createdDate'][5] . ':00')->toDateTimeString(),
                    'modify_time' => (new Carbon())->create($content['lastModifiedDate'][1] . '-' . $content['lastModifiedDate'][2] . '-' . $content['lastModifiedDate'][3] . ' ' . $content['lastModifiedDate'][4] . ':' . $content['lastModifiedDate'][5] . ':00')->toDateTimeString()
                ];
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * 事件列表.
     * @param $username
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
                ],
            ],
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
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * @param $username
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Cloud\Exceptions\HttpException
     */
    public function notes($username, int $offset = 1, int $limit = 20)
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'NOTE',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ],
            ],
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $list = [];
            foreach ($result['contents'] as $content) {
                $content['created'] = empty($content['created']) ? (new Carbon())->toDateTimeString() : (new Carbon())->create(date('Y-m-d H:i:s',intval($content['created'] / 1000)))->toDateTimeString();
                $content['modified'] = empty($content['modified']) ? (new Carbon())->toDateTimeString() :  (new Carbon())->create(date('Y-m-d H:i:s',intval($content['modified'] / 1000)))->toDateTimeString();
                $list[] = $content;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * 提醒事项.
     * @param $username
     */
    public function reminders($username): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'REMINDER_SUMMARY',
                ],
            ],
        ]);
        if (isset($result['contents']['Collections']) && is_array($result['contents']['Collections'])) {
            $list = [];
            foreach ($result['contents']['Collections'] as $content) {
                $list[] = $content;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    /**
     * 提醒事项详情
     * @param $username
     * @param string $guid
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws \Cloud\Exceptions\HttpException
     */
    public function reminder($username, string $guid = 'root', int $offset = 1, int $limit = 20)
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'REMINDER_DETAIL',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                    'rid' => $guid
                ],
            ],
        ]);
        if (isset($result['contents']['Reminders']) && is_array($result['contents']['Reminders'])) {
            $list = [];
            foreach ($result['contents']['Reminders'] as $key => $item) {
                $item['create_time'] = (new Carbon())->create($item['createdDate'][1] . '-' . $item['createdDate'][2] . '-' . $item['createdDate'][3] . ' ' . $item['createdDate'][4] . ':' . $item['createdDate'][5])->toDateTimeString();
                $list[$key] = $item;
            }
            return [
                'code' => 200,
                'msg' => 'ok',
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => 200,
            'msg' => 'ok',
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }
}
