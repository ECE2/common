## 说明

### 启动
* 本地有 PHP + Swoole 环境的情况
``` shell
sh start_hyerf.sh [dev (加上后可以本地开发热更新)] 
```

* 使用 Docker 启动
```shell
sh start_hyperf.sh docker
...
进入 docker 交互
/data/project # sh start_hyerf.sh [dev (加上后可以本地开发热更新)]
```

PS: 当本地开启多个后端接口服务时, 端口冲突的话, 使用以下命令修改端口后直接启动
```shell
docker run -it \
    -p 9501:9501 -p 9504:9504 \
    --privileged -u root \
    --entrypoint /bin/sh \
    -v $(pwd):/data/project \
    -w /data/project \
    hyperf/hyperf:8.0-alpine-v3.12-swoole
```

[Hyperf 官方文档](https://hyperf.wiki/2.2/#/)

### 要求
* 环境 PHP8

### 开发
1. 按照数据表生成对应 model service controller 文件
```shell
php bin/hyperf.php gen:code --with-comments {表名}
```
2. 初始化 mysql 表和数据
```shell
php bin/hyperf.php migrate --seed
```
3. 开发环境下 trace 收集是 100%, 看 db query 啥的在 zipkin 里, 拿着 api 返回的 traceId 值搜索
4. 注意: 使用 Inject 注解进来的实例是单例, 在开发时, 比如有一个成员变量 $a = 0, 单次 request $a ++ 后, 后面的 request 里 $a 不再是 0 (除非重启服务)

... 未完待续

### 本地调试
1. xdebug 使用 [yasd](https://github.com/swoole/yasd)
2. .env SCAN_CACHEABLE 为 true 才能 debug
3. 调试断点在 aop 的文件上时, 需要要打在代理文件上 (在 runtime/container/proxy 下))

### 特殊情况处理
1. 开发时, 当出现 hyperf 启动失败, 端口被占用时
> failed to listen server port[0.0.0.0:9501], Error: Address already in use[48]

可以使用
```shell
kill -9 $(ps aux | grep "php bin/hyperf" | awk '{print $2}')
```
