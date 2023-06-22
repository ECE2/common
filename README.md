## 公共的一些内容

### 安装

```shell
composer require ece2/common -W --ignore-platform-reqs
```

初始化, 仅仅用在刚刚创建的 hyperf 项目, 不然会覆盖项目代码

```shell
php bin/hyperf.php vendor:publish ece2/common -f
```

如果本地调试公共组件的话, 可以在 composer.json 里加入一下代码, 以及 url 按照本地目录有对应的公共组件项目代码, 然后 composer update

```
"repositories": {
    "ece2/common": {
        "type": "path",
        "url": "../common"
    }
}
```

### 增加的 command

> 按照数据表生成对应 model service controller 文件

```shell
php bin/hyperf.php gen:code --with-comments --refresh-fillable {表名}
```

### 用户身份传递

* (Http 相关) 鉴权走的 src/Aspect/AuthAspect, 根据注解 Auth($guard) 对应的 AuthAspect 里的 AuthenticationInterface 对应实现来 check token
* (RPC 相关) src/Aspect/JsonRpcIdTransferAspect 注入 RPC 请求前, 把当前 Http 写入上下文的用户信息写入 RPC 上下文
* (RPC 相关) src/Middleware/JsonRpcIdTransferMiddleware 接收到 RPC 请求, 从 RPC 请求上下文获取用户信息
