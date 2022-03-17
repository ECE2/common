
## (内部使用) 公共的一些内容

本项目和使用项目平级放置

安装

```shell
composer require ece2/common
```

在 composer.json 加上

```
"repositories": {
    "ece2/common": {
        "type": "path",
        "url": "../common"
    }
}
```

初始化, 仅仅用在刚刚创建的 hyperf 项目, 不然会覆盖项目代码

```shell
php bin/hyperf.php vendor:publish ece2/common -f
```
