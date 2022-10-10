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
//        make(\Ece2\Common\JsonRpc\Contract\SystemSubAppServiceInterface::class)->create(['name' => 'iot', 'entry' => '/iot']);

        // 注册菜单
        $systemMenuService = make(\Ece2\Common\JsonRpc\Contract\SystemMenuServiceInterface::class);
        foreach ([
//                     ['id' => 4800, 'parent_id' => 0, 'level' => '0', 'name' => '设备管理', 'code' => 'iot:deviceManagement', 'icon' => 'ma-icon-tool', 'route' => 'iot/device-management', 'component' => '', 'is_hidden' => 1, 'type' => 'M', 'status' => 0, 'sort' => 0, 'only_super_admin_visible' => false],
                 ] as $menu) {
            $systemMenuService->create($menu);
        }
    }
}
