<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;

class Initialization extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 注册子应用
        // 注意: 这里 'entry' => '/' . env('APP_NAME'), 这么设置在部署时, nginx 反向代理后, 域名/项目 是可以的. 但是在开发环境不行, 这里可以在开发环境手动在数据库里修改 entry 值为 h
//        make(\Ece2\Common\JsonRpc\Contract\SystemSubAppServiceInterface::class)->create(['name' => env('APP_NAME'), 'entry' => '/' . env('APP_NAME')]);

        // 注册菜单
        $systemMenuService = make(\Ece2\Common\JsonRpc\Contract\SystemMenuServiceInterface::class);
        /**
         * id menu_id
         * parent_id 上级 ID
         * level 上级 ID 路径; "," 相隔, 最顶级为 "0", 从左到右依次从顶往下: 0,1000,1001
         * name 菜单/按钮 名称
         * code 唯一 code
         * icon
         * route 前端网页路径
         * component 组件; 菜单为空, 子应用的菜单或者说页面使用 "@/layout/micro" 打开微前端入口
         * redirect 跳转地址
         * is_hidden 是否隐藏 (0是 1否)
         * type 菜单类型, (M菜单 B按钮 L链接 I iframe)
         * status 状态 (0正常 1停用)
         * sort 排序
         * remark 备注
         * only_super_admin_visible 仅超管可见 true/false
         */
        foreach ([
//                     ['id' => 4800, 'parent_id' => 0, 'level' => '0', 'name' => '设备管理', 'code' => 'iot:deviceManagement', 'icon' => 'ma-icon-tool', 'route' => 'iot/device-management', 'component' => '', 'is_hidden' => 1, 'type' => 'M', 'status' => 0, 'sort' => 0, 'only_super_admin_visible' => false],
                 ] as $menu) {
            $systemMenuService->create($menu);
        }
    }
}
