<?php
/**
 * RoleController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 00:37
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminNode;
use app\admin\entity\AdminRole;
use app\admin\entity\AdminUser;
use Cdyun\PhpTool\Arr;

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
            $pIds = Arr::getParentIds($result->toArray(), $include,'pid');
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

}