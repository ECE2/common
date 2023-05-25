<?php

declare(strict_types=1);

namespace Ece2\Common\Model\Traits;

/**
 * 临时数据库相关操作
 */
trait DbHelp
{

    /**
     * 根据配置连接数据库，一般用来临时连接三方数据库
     * @param object|array $params
     * @return \PDO
     */
    public function connectionDb(object|array $params): \PDO
    {

        return new \PDO($params['dsn'], $params['username'], $params['password'], [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }
}
