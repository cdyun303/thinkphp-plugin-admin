<?php
/**
 * 角色管理
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 00:37
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminNode;
use app\admin\entity\AdminRole;
use app\admin\entity\AdminUser;
use app\admin\validate\AdminRoleValidate;
use Cdyun\PhpTool\Arr;

#[NodeGroup(Type::NodeGroup['sys'])]
#[NodeItem(['title' => '角色管理', 'href' => '/admin/core/role/index', 'weight' => 900])]
class RoleController extends AdminBaseController
{

    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch();
    }

    /**
     * 查询
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
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
            success(Arr::tree($result, 'id', 'pid', 'children'));
        }
        $query = $query->paginate($limit);
        $items = $query->items();
        paginate($items, $query->total());
    }

    /**
     * 新增
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function create(): string
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            validate_data($params, AdminRoleValidate::class, 'create');

            $userEntity = new AdminUser();
            $roleEntity = new AdminRole();

            if ($roleEntity->where('name', $params['name'])->find()) {
                error('角色标识已存在');
            }
            if (!$userEntity->isSuperAdmin() && !in_array($params['pid'], $roleEntity->getScopeRoleIds(true))) {
                error('父级角色组超出权限范围');
            }
            // 检查权限字典是否合法
            $roleEntity->checkRules((int)$params['pid'], $params['rules'] ?? '');
            // 创建角色
            $roleEntity->create($params);
            success();
        }
        return $this->fetch();
    }

    /**
     * 编辑
     * @return string|void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function edit()
    {
        if ($this->request->isGet()) {
            return $this->fetch();
        }
        $params = $this->request->post();
        validate_data($params, AdminRoleValidate::class, 'create');

        $userEntity = new AdminUser();
        $roleEntity = new AdminRole();
        $scopeRoleIds = $roleEntity->getScopeRoleIds();
        $isSuperAdmin = $userEntity->isSuperAdmin();
        if (!$isSuperAdmin && !in_array($params['id'], $scopeRoleIds)) {
            error('无数据权限');
        }
        $role = $roleEntity->where('id', $params['id'])->find();
        if (!$role) {
            error('数据不存在');
        }

        $isSupperRole = $role->rules === '*';
        // 超级角色组不允许更改rules pid 字段
        if ($isSupperRole) {
            unset($params['rules'], $params['pid']);
        }

        if (key_exists('pid', $params)) {
            $pid = $params['pid'];
            if (!$pid) {
                error('请选择父级角色组');
            }
            if ($pid == $params['id']) {
                error('父级不能是自己');
            }
            if (!$isSuperAdmin && !in_array($pid, $roleEntity->getScopeRoleIds(true))) {
                error('父级超出权限范围');
            }
        } else {
            $pid = $role->pid;
        }

        if (!$isSupperRole) {
            // 检查权限字典是否合法
            $roleEntity->checkRules((int)$pid, $params['rules'] ?? '');
        }
        $roleEntity->update($params, ['id' => $params['id']]);

        // 删除所有子角色组中已经不存在的权限
        if (!$isSupperRole) {
            $roleRules = $role->rules ? explode(',', $role->rules) : [];
            $listRoles = $roleEntity->field('id,pid')->select()->toArray();
            $childIds = Arr::getChildIds($listRoles, $params['id'], 'pid');
            foreach ($childIds as $vo) {
                $tmpRole = $roleEntity->find($vo);
                $tmpRoleRules = array_intersect(explode(',', $tmpRole->rules), $roleRules);
                $tmpRole->rules = implode(',', $tmpRoleRules);
                $tmpRole->save();
            }
        }
        success();
    }

    /**
     * 获取角色权限
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
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
            $pIds = Arr::getParentIds($result->toArray(), $include, 'pid');
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
     * 删除
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function delete(): void
    {
        $ids = $this->request->post('id', []);
        if (empty($ids)) {
            error('参数错误');
        }
        $ids = is_array($ids) ? $ids : [$ids];
        if (in_array(1, $ids)) {
            error('无法删除超级管理员角色');
        }

        $userEntity = new AdminUser();
        $roleEntity = new AdminRole();
        if (!$userEntity->isSuperAdmin() && array_diff($ids, $roleEntity->getScopeRoleIds())) {
            error('无删除权限');
        }

        $listRoles = $roleEntity->field('id,pid')->select()->toArray();
        $childIds = Arr::getChildIds($listRoles, $ids, 'pid', true);
        $roleEntity->destroy($childIds);
        success();
    }
}