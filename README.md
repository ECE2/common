## 公共的一些内容

安装

```shell
composer require ece2/common
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
