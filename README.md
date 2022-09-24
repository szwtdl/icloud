# Apple icloud sdk v2

[![Build Status](https://github.com/szwtdl/icloud/actions/workflows/test.yml/badge.svg)](https://github.com/szwtdl/icloud/actions)
[![Latest Stable Version](https://poser.pugx.org/szwtdl/icloud/v/stable)](https://packagist.org/packages/szwtdl/icloud)
[![Total Downloads](https://poser.pugx.org/szwtdl/icloud/downloads)](https://packagist.org/packages/szwtdl/icloud)
[![Latest Unstable Version](https://poser.pugx.org/szwtdl/icloud/v/unstable)](https://packagist.org/packages/szwtdl/icloud)
[![License](https://poser.pugx.org/szwtdl/icloud/license)](https://packagist.org/packages/szwtdl/icloud)
[![Monthly Downloads](https://poser.pugx.org/szwtdl/icloud/d/monthly)](https://packagist.org/packages/szwtdl/icloud)

### Cloud v2 版本和v1版本没法共同使用

```bash
use Cloud\Application;
$options = [
    'client_id' => 'demo',
    'client_key' => md5('demo'),
    'options' => [
        'base_uri' => 'http://localhost:8080',
    ],
];
$application = new Application($options);

```

### laravel

`config/services.php`

```bash
'icloud' = [
    'client_id' = 'app_id',
    'client_key' = 'app_key',
    'domain' = 'https://icloud.test.com' #授权域名
];
```

```bash
$application = app('icloud')->login('xxx@gmail.com',12345678);
```

### 登录账号

```bash
$application->login("demo@gmail.net", "demo123","device_id")
```

### 二步验证

```bash
$application->verify("demo@gmail.net", "demo123","888888","device_id");
```

### 下载数据

```bash
$application->download("demo@gmail.net", "demo123");
```

### 重置session

```bash
$application->reset("demo@gmail.net");
```

### 账号数据

```bash 
$application->account("demo@gmail.net");
```

### 联系人

```bash
$application->contact("demo@gmail.net",1,20);
```

### 相册列表

```bash
$application->albums("demo@gmail.net",1,20);
```

### 文件数据

```bash
$application->files("demo@gmail.net",1,20);
```

### 定位列表

```bash
$application->location("demo@gmail.net",1,20);
```

### 日历列表

```bash
$application->calendar("demo@gmail.net");
```

### 提醒事项

```bash
$application->reminders("demo@gmail.net");
```

### 备注

```bash 
$application->reminders("demo@gmail.net");
```

### 短信列表

```bash 
$application->TextMessages("demo@gmail.net");
```

### 单个用户的短信列表

```bash 
$application->TextMessage($username, '+447563696391');
```