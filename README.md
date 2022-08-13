# Apple icloud sdk

### Cloud

```bash
use Cloud\Factory;

$application = Factory::make('icloud', ['client_id' => 'demo', 'client_key' => md5('demo'), 'domain' => 'http://localhost:8080']);

```

### 登录账号

```bash
$application->login("demo@gmail.net", "demo123")
```

### 二步验证

```bash
$application->verify("demo@gmail.net", "demo123","888888");
```

### 重置session

```bash
$application->reset("demo@gmail.net");
```

### 下载数据

```bash
$application->download("demo@gmail.net", "demo123");
```

### 联系人

```bash
$application->contact("demo@gmail.net",1,20);
```
### 相册列表
```bash
$application->albums("demo@gmail.net",1,20);
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

### 事件列表
```bash
$application->events("demo@gmail.net");
```

### 文件数据
```bash
$application->files("demo@gmail.net");
```

###  
