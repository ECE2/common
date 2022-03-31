## 说明

### 启动
``` 
sh start_hyerf.sh [dev (加上后可以本地开发热更新)] 
```

[Hyperf 官方文档](https://hyperf.wiki/2.2/#/)

### 要求
* 环境 PHP8

### 本地调试
1. xdebug 使用 [yasd](https://github.com/swoole/yasd)
2. .env SCAN_CACHEABLE 为 true 才能 debug
3. 调试断点在 aop 的文件上时, 需要要打在代理文件上 (在 runtime/container/proxy 下))

### 特殊情况处理
1. 开发时, 当出现 hyperf 启动失败, 端口被占用时
> failed to listen server port[0.0.0.0:9501], Error: Address already in use[48]

可以使用
```
kill -9 $(ps aux | grep "php bin/hyperf" | awk '{print $2}')
```
