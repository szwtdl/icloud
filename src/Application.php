<?php

declare(strict_types=1);
/**
 * This file is part of szwtdl/icloud
 * @link     https://www.szwtdl.cn
 * @contact  szpengjian@gmail.com
 * @license  https://github.com/szwtdl/icloud/blob/master/LICENSE
 */

namespace Cloud;

use Carbon\Carbon;

class Application
{
    public const SUCCESS = 200;

    public const ERROR = 201;

    public const SUCCESS_MSG = 'ok';

    public const ERROR_MSG = 'fail';

    public HttpRequest $request;

    public Carbon $now;

    public array $options = [];

    public function __construct(array $config)
    {
        $this->request = new HttpRequest($config);
        $this->now = new Carbon();
    }

    public function login(string $username, string $password, int $device_id = 0)
    {
        $json = ['username' => $username, 'password' => $password];
        if ($device_id !== 0) {
            $json['verifyType'] = 'sms';
            $json['deviceid'] = (string)$device_id;
        }
        $result = $this->request->postJson('/v2/api/auth', ['json' => $json]);
        if (isset($result['status'], $result['ec'], $result['em'])) {
            $response = [];
            switch ($result['ec']) {
                case 10000:
                    $response = [
                        'code' => self::SUCCESS,
                        'msg' => $result['em'],
                        'data' => $result['em'],
                    ];
                    break;
                case 10003:
                    $response = [
                        'code' => self::ERROR,
                        'msg' => $result['em'],
                        'data' => $result,
                    ];
                    break;
                case 10004:
                    $response = [
                        'code' => 202,
                        'msg' => $result['em'],
                        'data' => $this->phones($username, $password),
                    ];
                    break;
            }
            return $response;
        }
    }

    public function verify(string $username, string $password, string $code, int $device_id = 0)
    {
        $json = ['username' => $username, 'password' => $password, 'securityCode' => $code];
        if ($device_id !== 0) {
            $json['verifyType'] = 'sms';
            $json['deviceid'] = (string)$device_id;
        }
        $result = $this->request->postJson('v2/api/auth/verify', ['json' => $json]);
        if (isset($result['status'], $result['ec'], $result['em'])) {
            switch ($result['ec']) {
                case 10004:
                case 10001:
                    return [
                        'code' => 202,
                        'msg' => $result['status'],
                        'data' => [
                            'status' => $result['status'],
                            'code' => $result['ec'],
                            'msg' => $result['em'],
                        ],
                    ];
                    break;
                case 10000:
                    return [
                        'code' => self::SUCCESS,
                        'msg' => self::SUCCESS_MSG,
                        'data' => [
                            'status' => $result['status'],
                            'code' => $result['ec'],
                            'msg' => $result['em'],
                        ],
                    ];
                    break;
            }
        }
    }

    public function reset(string $username): array
    {
        $result = $this->request->postJson('v2/api/auth/reset', [
            'json' => ['username' => $username],
        ]);
        if (isset($result['ec']) && $result['ec'] === 200) {
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => $result,
            ];
        }
        return [
            'code' => self::ERROR,
            'msg' => self::ERROR_MSG,
            'data' => $result,
        ];
    }

    public function download(string $username, string $password): array
    {
        $result = $this->request->postJson('v2/api/download', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);
        if (isset($result['status'], $result['ec']) && $result['ec'] === 200) {
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => $result['em'],
            ];
        }
        return [
            'code' => self::ERROR,
            'msg' => self::ERROR_MSG,
            'data' => $result,
        ];
    }

    public function phones(string $username, string $password): array
    {
        $result = $this->request->postJson('/v2/api/auth/authinfo', ['json' => ['username' => $username, 'password' => $password]]);
        $data = [
            'device_type' => false,
            'phone' => [],
            'phones' => [],
        ];
        if (isset($result['direct'])) {
            $array = $result['direct']['twoSV']['phoneNumberVerification']['trustedPhoneNumbers'];
            $trustedPhoneNumber = $result['direct']['twoSV']['phoneNumberVerification']['trustedPhoneNumber'];
            $data['device_type'] = $result['direct']['hasTrustedDevices'] === true ? 'none' : 'sms';
            $data['phone'] = [
                'id' => $trustedPhoneNumber['id'],
                'phone' => $trustedPhoneNumber['numberWithDialCode'],
                'last' => $trustedPhoneNumber['lastTwoDigits'],
            ];
            foreach ($array as $item) {
                $data['phones'][] = [
                    'id' => $item['id'] ?? '',
                    'phone' => $item['numberWithDialCode'] ?? '',
                    'last' => $item['lastTwoDigits'] ?? '',
                ];
            }
        }
        return $data;
    }

    public function account(string $username): array
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
                if ($device['model'] == 'PC' && $device['modelDisplayName'] == 'Windows') {
                    continue;
                }
                $list[] = $device;
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => $list,
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [],
        ];
    }

    public function contact(string $username, int $offset = 1, int $limit = 20): array
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
                // 通话记录
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
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    public function TextMessages(string $username, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'SMS_CHAT_SUMMARY',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                ],
            ],
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $arr = [];
            foreach ($result['contents'] as $content) {
                $arr[] = [
                    'name' => trim($content['chatid']),
                    'snippet' => str_replace('<#>', '', trim($content['snippet'])),
                    'time' => $content['snippetTimestamp'],
                    'count' => $content['messageCount'],
                    'last' => $content['lastMessageSeq'],
                ];
            }

            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'data' => $arr,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [],
        ];
    }

    public function TextMessage(string $username, string $rid, int $offset = 1, int $limit = 20): array
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'SMS_CHAT_DETAIL',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                    'rid' => 'SMS;-;' . $rid,
                ],
            ],
        ]);
        if (isset($result['contents']) && is_array($result['contents'])) {
            $arr = [];
            foreach ($result['contents'] as $content) {
                $arr[] = [
                    'id' => $content['guid'],
                    'time' => $content['date'],
                    'text' => str_replace('<#>', '', trim($content['text'])),
                    'type' => $content['service'],
                    'status' => $content['isDelivered'],
                    'is_emote' => $content['isEmote'],
                    'is_from_me' => $content['isFromMe'],
                    'is_attachment' => $content['isAttachment'],
                    'attachment' => [
                        'id' => $content['attachment']['guid'],
                        'mimeType' => $content['attachment']['mimeType'],
                        'transferState' => $content['attachment']['transferState'],
                        'isOutgoing' => $content['attachment']['isOutgoing'],
                        'url' => $content['attachment']['url'],
                    ],
                ];
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'data' => $arr,
                ],
            ];
        }
        return [
            'code' => self::ERROR,
            'msg' => self::ERROR_MSG,
            'data' => [],
        ];
    }

    public function location(string $username, int $offset = 1, int $limit = 20): array
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
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    public function albums(string $username, int $offset = 1, int $limit = 20, $name = 'All Photos'): array
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
            foreach ($result['contents'] as $key => $content) {
                $type = strtolower(pathinfo($content['medium']['url'])['extension']);
                if (in_array($type, ['mov', 'mp4'])) {
                    $list[] = [
                        'id' => md5($content['id']),
                        'filename' => str_replace('./', '', getEscape($content['filename'])),
                        'type' => strtolower(pathinfo($content['medium']['url'])['extension']),
                        'created' => $this->now->create($content['created'])->toDateTimeString(),
                        'poster' => isset($content['cover']['url']) ? trim($content['cover']['url']) : '',
                        'duration' => isset($content['cover']['duration']) ? trim($content['cover']['duration']) : '0.00',
                        'original' => getEscape($content['medium']['url']),
                        'url' => getEscape($content['thumb']['url']),
                    ];
                } else {
                    $list[] = [
                        'id' => md5($content['id']),
                        'filename' => str_replace('./', '', getEscape($content['filename'])),
                        'type' => strtolower(pathinfo($content['medium']['url'])['extension']),
                        'created' => $this->now->create($content['created'])->toDateTimeString(),
                        'original' => getEscape($content['medium']['url']),
                        'url' => getEscape($content['thumb']['url']),
                    ];
                }
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    public function files(string $username, string $name = 'root'): array
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
                ],
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
                            'create_time' => $this->now->create($child['dateCreated'])->toDateTimeString(),
                            'modify_time' => $this->now->create($child['dateModified'])->toDateTimeString(),
                            'update_time' => $this->now->create($child['dateChanged'])->toDateTimeString(),
                            'last_time' => $this->now->create($child['lastOpenTime'])->toDateTimeString(),
                            'url' => empty($child['url']) ? '' : $child['url'],
                        ];
                    } else {
                        $data['list'][$key] = [
                            'id' => $child['docwsid'],
                            'name' => urldecode($child['name']),
                            'parentId' => str_replace('FOLDER::com.apple.CloudDocs::', '', $child['parentId']),
                            'type' => strtolower($child['type']),
                            'icon' => empty($child['extension']) ? 'rar' : getExtension($child['extension']),
                            'create_time' => $this->now->create($child['dateCreated'])->toDateTimeString(),
                            'modify_time' => $this->now->create($child['dateModified'])->toDateTimeString(),
                            'update_time' => $this->now->create($child['dateChanged'])->toDateTimeString(),
                            'last_time' => $this->now->create($child['lastOpenTime'])->toDateTimeString(),
                            'url' => empty($child['url']) ? '' : $child['url'],
                            'size' => empty($child['size']) ? '0KB' : format_size($child['size']),
                            'extension' => empty($child['extension']) ? '' : trim($child['extension']),
                        ];
                    }
                }
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => $data,
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'folder' => [],
                'list' => [],
            ],
        ];
    }

    public function calendar(string $username, int $offset = 1, int $limit = 20): array
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
                    'create_time' => $this->now->create($content['createdDate'][1] . '-' . $content['createdDate'][2] . '-' . $content['createdDate'][3] . ' ' . $content['createdDate'][4] . ':' . $content['createdDate'][5] . ':00')->toDateTimeString(),
                    'modify_time' => $this->now->create($content['lastModifiedDate'][1] . '-' . $content['lastModifiedDate'][2] . '-' . $content['lastModifiedDate'][3] . ' ' . $content['lastModifiedDate'][4] . ':' . $content['lastModifiedDate'][5] . ':00')->toDateTimeString(),
                ];
            }
            // 默认用日历数据，如果有事件，就显示事件列表
            $res = $this->events($username, $offset, $limit);
            $data = [
                'calendar' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
                'total' => $res['data']['total'],
                'list' => $res['data']['list'],
            ];
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => $data,
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
                'calendar' => [
                    'total' => 0,
                    'list' => [],
                ],
            ],
        ];
    }

    public function events(string $username, int $offset = 1, int $limit = 20): array
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
            foreach ($result['contents'] as $key => $item) {
                $list[] = [
                    'title' => $item['title'],
                    'type' => $item['pGuid'],
                    'order' => $key + 1,
                    'borderColor' => '',
                    'backgroundColor' => '',
                    'tz' => $item['tz'],
                    'tzname' => $item['tzname'],
                    'allDay' => $item['allDay'],
                    'startDateTZOffset' => $item['startDateTZOffset'],
                    'duration' => $item['duration'],
                    'start' => $this->now->create($item['startDate'][1] . '-' . $item['startDate'][2] . '-' . $item['startDate'][3] . ' ' . $item['startDate'][4] . ':' . $item['startDate'][5] . ':00')->toDayDateTimeString(),
                    'end' => $this->now->create($item['endDate'][1] . '-' . $item['endDate'][2] . '-' . $item['endDate'][3] . ' ' . $item['endDate'][4] . ':' . $item['endDate'][5] . ':00')->toDayDateTimeString(),
                ];
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    public function notes(string $username, int $offset = 1, int $limit = 20)
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
                $content['created'] = empty($content['created']) ? $this->now->toDateTimeString() : $this->now->create(date('Y-m-d H:i:s', intval($content['created'] / 1000)))->toDateTimeString();
                $content['modified'] = empty($content['modified']) ? $this->now->toDateTimeString() : $this->now->create(date('Y-m-d H:i:s', intval($content['modified'] / 1000)))->toDateTimeString();
                $list[] = $content;
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }

    public function reminders(string $username, string $rid = 'root', int $offset = 1, int $limit = 20): array
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
            $menu = [];
            foreach ($result['contents']['Collections'] as $key => $content) {
                $menu[$key] = [
                    'id' => $content['guid'],
                    'title' => $content['title'],
                    'order' => $content['order'],
                    'color' => $content['color'],
                    'symbolicColor' => $content['symbolicColor'],
                    'create_time' => $this->now->create($content['createdDate'][1] . '-' . $content['createdDate'][2] . '-' . $content['createdDate'][3] . ' ' . $content['createdDate'][4] . ':' . $content['createdDate'][5] . ':00')->toDateTimeString(),
                    'enabled' => $content['enabled'],
                    'active' => false,
                    'total' => $content['totalCount'],
                ];
                if ($rid === 'root' || $content['guid'] === 'tasks') {
                    $menu[$key]['active'] = true;
                }
            }
            // 按照字段重新排序
            $sort = array_column($menu, 'order');
            array_multisort($sort, SORT_ASC, $menu);
            if ($rid == 'root') {
                $rid = $menu[0]['id'];
            }
            $res = $this->reminder($username, $rid, $offset, $limit);
            $data['menu'] = $menu;
            $data['total'] = $res['data']['total'];
            $data['list'] = $res['data']['list'];
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => $data,
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
                'menu' => [],
            ],
        ];
    }

    private function reminder(string $username, string $guid = 'root', int $offset = 1, int $limit = 20)
    {
        $result = $this->request->postJson('v2/api/database/retrieve', [
            'json' => [
                'username' => $username,
                'params' => [
                    'category' => 'REMINDER_DETAIL',
                    'offset' => ($offset - 1) * $limit,
                    'limit' => $limit,
                    'rid' => $guid,
                ],
            ],
        ]);
        if (isset($result['contents']['Reminders']) && is_array($result['contents']['Reminders'])) {
            $list = [];
            foreach ($result['contents']['Reminders'] as $key => $item) {
                $list[$key] = [
                    'id' => $item['guid'],
                    'title' => $item['title'],
                    'type' => $item['pGuid'],
                    'modify_time' => $this->now->create($item['lastModifiedDate'][1] . '-' . $item['lastModifiedDate'][2] . '-' . $item['lastModifiedDate'][3] . ' ' . $item['lastModifiedDate'][4] . ':' . $item['lastModifiedDate'][5])->toDateTimeString(),
                    'create_time' => $this->now->create($item['createdDate'][1] . '-' . $item['createdDate'][2] . '-' . $item['createdDate'][3] . ' ' . $item['createdDate'][4] . ':' . $item['createdDate'][5])->toDateTimeString(),
                ];
            }
            return [
                'code' => self::SUCCESS,
                'msg' => self::SUCCESS_MSG,
                'data' => [
                    'total' => $result['totalCount'],
                    'list' => $list,
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'msg' => self::SUCCESS_MSG,
            'data' => [
                'total' => 0,
                'list' => [],
            ],
        ];
    }
}
