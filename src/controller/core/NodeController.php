<?php
/**
 * NodeController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 23:38
 */

declare (strict_types=1);

namespace Thinkphp\Admin\controller\core;

use Thinkphp\Admin\controller\AdminBaseController;
use Thinkphp\Admin\entity\AdminNode;
use Thinkphp\Admin\entity\AdminRole;
use Cdyun\PhpTool\Arr;
use function app\admin\controller\core\success;

class NodeController extends AdminBaseController
{
    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch('node/index');
        
    }
    /**
     * 获取角色菜单节点
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function get(): void
    {
        $roleEntity = new AdminRole();
        $rules = $roleEntity->getRoleRules(admin('roles'));
        $types = $this->request->get('type', '0,1');
        $types = is_string($types) ? explode(',', $types) : [0, 1];
        $nodeEntity = new AdminNode();
        $nodes = $nodeEntity->order('weight', 'desc')->select()->toArray();

        $fmtNodes = [];
        foreach ($nodes as $item) {
            $item['pid'] = (int)$item['pid'];
            $item['name'] = $item['title'];
            $item['value'] = $item['id'];
            $item['icon'] = $item['icon'] ? "layui-icon {$item['icon']}" : '';
            $fmtNodes[] = $item;
        }
        $nodeTree=Arr::toTree($fmtNodes, 0, 'id','pid');

        // 超级管理员权限为 *
        if (!in_array('*', $rules)) {
            $nodeEntity->removeNotContain($nodeTree, 'id', $rules);
        }
        $nodeEntity->removeNotContain($nodeTree, 'type', $types);
        $menus = $nodeEntity->empty_filter($nodeTree);
        success($menus);
    }

    public function permission()
    {
        $roleEntity = new AdminRole();
        $rules = $roleEntity->getRoleRules(admin('roles'));
        // 超级管理员
        if (in_array('*', $rules)) {
            success(['*']);
        }
    }
}