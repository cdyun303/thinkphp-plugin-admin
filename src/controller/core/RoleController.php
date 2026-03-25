<?php
/**
 * RoleController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 00:37
 */

declare (strict_types=1);

namespace Thinkphp\Admin\controller\core;

use Thinkphp\Admin\controller\AdminBaseController;
use Thinkphp\Admin\entity\AdminNode;
use Thinkphp\Admin\entity\AdminRole;
use Thinkphp\Admin\entity\AdminUser;
use Cdyun\PhpTool\Arr;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use function app\admin\controller\core\error;
use function app\admin\controller\core\paginate;
use function app\admin\controller\core\success;

class RoleController extends AdminBaseController
{

    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch('role/index');
    }

    /**
     * 查询
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function list(): void
    {
        $id = $this->request->get('id');
        $entity = new AdminRole();
        [$where, $format, $limit, $field, $order] = $entity->doSearchSelect($this->request);
        $limit = 100000;

        $roleIds = $entity->getScopeRoleIds(true);
        if (!$id) {
            $where['id'] = ['in', $roleIds];
        } elseif (!in_array($id, $roleIds)) {
            error('无权限');
        }
        $query = $entity->doQueryWhere($where, $field, $order);

        if ($format == 'select') {
            $data = $query->select();
            $result = [];
            foreach ($data as $item) {
                $result[] = [
                    'id' => $item['id'],
                    'pid' => $item['pid'],
                    'name' => $item['title'],
                    'value' => $item['id'],
                ];
            }
            success(Arr::toTree($result, 0, 'id', 'pid', 'children'));
        }
        $query = $query->paginate($limit);
        $items = $query->items();
        paginate($items, $query->total());
    }

    public function create()
    {
        if ($this->request->isPost()) {
            success();
        }
        return $this->fetch('role/create');
    }

    public function edit()
    {
        if ($this->request->isPost()) {
            success();
        }
        return $this->fetch('role/edit');
    }

    /**
     * 获取角色权限
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function rules(): void
    {
        $roleId = $this->request->get('id');
        if (empty($roleId)) {
            success([]);
        }
        $userEntity = new AdminUser();
        $roleEntity = new AdminRole();
        if (!$userEntity->isSuperAdmin() && !in_array($roleId, $roleEntity->getScopeRoleIds(true))) {
            error('角色组超出权限范围');
        }

        $ruleIds = $roleEntity->where('id', $roleId)->value('rules');
        if ($ruleIds === '') {
            success([]);
        }

        $nodeEntity = new AdminNode();
        $result = $nodeEntity->select();

        if ($ruleIds !== '*') {
            $include = explode(',', $ruleIds);
            // 获取所有父级 ID
            $pIds = $this->getParentIdsFlat($result->toArray(), $include);
            // 合并父级和子级 ID 并去重
            $arrIds = array_unique(array_merge($pIds, $include));
            // 数据集查询
            $result = $result->whereIn('id', array_map('intval', $arrIds));
        }
        $items = [];
        foreach ($result as $item) {
            $items[] = [
                'name' => $item['title'] ?? $item['name'] ?? $item['id'],
                'value' => (string)$item['id'],
                'id' => $item['id'],
                'pid' => $item['pid'],
            ];
        }

        success(Arr::tree($items, 'id', 'pid', 'children'));
    }

    /**
     * 获取叶子节点的所有父节点 ID
     * @param array $tree 树形结构数组
     * @param int|string|array $leafId 叶子节点 ID，支持单个 ID 或 ID 数组
     * @return array 返回所有父节点 ID 数组
     * @author cdyun(121625706@qq.com)
     */
    protected function getParentIdsFlat(array $tree, int|string|array $leafId)
    {
        // 支持数组输入
        if (is_array($leafId)) {
            $allParentIds = [];
            foreach ($leafId as $id) {
                $parentIds = $this->getParentIdsFlat($tree, $id);
                $allParentIds = array_merge($allParentIds, $parentIds);
            }
            return array_values(array_unique($allParentIds));
        }

        $map = [];
        foreach ($tree as $node) {
            $map[$node['id']] = $node;
        }

        if (!isset($map[$leafId])) {
            return [];
        }

        $parentIds = [];
        $currentId = $leafId;

        $max_depth = 100;
        while ($max_depth-- > 0 && isset($map[$currentId]) && $map[$currentId]['pid'] != 0 && $map[$currentId]['pid'] !== null) {
            $parentId = $map[$currentId]['pid'];

            // 防止死循环（数据脏了的情况）
            if ($parentId == $currentId) {
                break;
            }

            $parentIds[] = $parentId;
            $currentId = $parentId;

            if (!isset($map[$currentId])) {
                break;
            }
        }

        return $parentIds;
    }
}