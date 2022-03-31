#!/bin/sh

# 判断是否在 docker 容器内, 兼容本地环境直接启动 hyperf
if [ -f /.dockerenv ]; then
  cd '/data/project' || exit
else
  # shellcheck disable=SC2046
  cd $(dirname "$0") || exit
fi

run_in_docker="$1" # 是否 Docker 启动
if [ "$run_in_docker" = "docker" ]; then
  docker run -it \
    -p 9501:9501 -p 9504:9504 \
    --privileged -u root \
    --entrypoint /bin/sh \
    -v $(pwd):/data/project \
    -w /data/project \
    hyperf/hyperf:8.0-alpine-v3.12-swoole
  exit
fi

composer update
composer dump-autoload -o # 删除 aop 文件等
composer run-script cs-fix # 格式化
composer run-script post-root-package-install # 判断 .env 是否存在

app_env="$1" # 根据环境判断是否开启热更新
if [ "$app_env" = "dev" ]; then
  php bin/hyperf.php migrate --seed # 数据库初始化
  php bin/hyperf.php server:watch # 开发环境热更新
else
  php bin/hyperf.php start
fi
