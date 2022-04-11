<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;
use \Ece2\Common\Middleware\ValidateTokenMiddleware;
use \Ece2\Common\Middleware\PermissionMiddleware;

Router::get('/favicon.ico', static function () {
    return '';
});

/*
 * [Method 使用建议]
 * GET 一般用在获取详情列表之类, 做获取的操作; 比如获取角色列表: GET /api/admin/role (params)
 * POST 一般用在新增操作; 比如新增角色: POST /api/admin/role (data)
 * PUT 一般用在更新操作; 比如编辑角色: PUT /api/admin/role (data)
 * DELETE 一般用在删除操作; 比如删除角色: DELETE /api/admin/role
 * 在获取详情或者删除某一条数据操作接口上, 建议使用类似 /api/admin/role/{id} 详见: https://hyperf.wiki/2.2/#/zh-cn/router?id=%e8%b7%af%e7%94%b1%e5%8f%82%e6%95%b0
 */
Router::addGroup('/api', static function () {
    Router::addRoute(['GET', 'POST'], '/', 'App\Controller\IndexController@index');
}, ['middleware' => [ValidateTokenMiddleware::class, PermissionMiddleware::class]]);
