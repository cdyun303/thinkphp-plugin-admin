<?php
/**
 * AdminRole.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 23:53
 */

declare (strict_types=1);

namespace app\admin\entity;

use app\admin\common\exception\AdminException;
use app\admin\model\AdminUserRole;
use Cdyun\PhpTool\Arr;
use support\base\BaseEntity;

class AdminRole extends BaseEntity
{
    /**
     * 获取权限范围内的所有管理员id
     * @param bool $with_self - 是否包含自身
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getScopeAdminIds(bool $with_self = false): array
    {
        $roleIds = $this->getScopeRoleIds();
        $adminIds = (new AdminUserRole)->whereIn('role_id', $roleIds)->column('admin_id');
        if ($with_self) {
            $adminIds[] = admin('id');
        }
        return array_unique($adminIds);
    }

    /**
     * 获取权限范围内的所有角色id
     * @param bool $with_self
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getScopeRoleIds(bool $with_self = false): array
    {
        if (!$admin = admin()) {
            return [];
        }
        try {
            $roleIds = $admin['roles'];
            $rules = $this->whereIn('id', $roleIds)->column('rules');
            // 超级管理员 * 权限
            if ($rules && in_array('*', $rules)) {
                return $this->column('id'); // 获取所有角色id
            }

            // 非超级管理员
            $roles = $this->field('id,pid,name,title')->select()->toArray();
            $result = $with_self ? $roleIds : []; // 是否包含自身
            foreach ($roleIds as $item) {
                $itemTree = Arr::toTree($roles, $item, 'id', 'pid'); // 获取指定根节点的树形结构
                $itemData = Arr::list($itemTree); // 树形结构转数组
                $itemIds = Arr::column($itemData, 'id'); // 提取数组中id列
                $result = Arr::unique(Arr::merge($result, $itemIds)); // 合并并去重
            }
            return $result;

        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
    }

    /**
     * 检查权限字典是否合法
     * @param int $roleId - 角色id
     * @param string $ruleIds - 权限id
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function checkRules(int $roleId, string $ruleIds): void
    {
        if ($ruleIds) {
            $ruleIds = explode(',', $ruleIds);
            if (in_array('*', $ruleIds)) {
                throw new AdminException('非法数据');
            }
            $nodeEntity = new AdminNode();
            $rule_exists = $nodeEntity->whereIn('id', $ruleIds)->column('id');
            if (count($rule_exists) != count($ruleIds)) {
                throw new AdminException('权限不存在');
            }
            $roleEntity = new AdminRole();
            $rule_id_string = $roleEntity->where('id', $roleId)->value('rules');
            if ($rule_id_string === '') {
                throw new AdminException('数据超出权限范围');
            }
            if ($rule_id_string === '*') {
                return;
            }
            $legal_rule_ids = explode(',', $rule_id_string);
            if (array_diff($ruleIds, $legal_rule_ids)) {
                throw new AdminException('数据超出权限范围');
            }
        }
    }

    /**
     * 获取角色的权限码
     * @param $roles
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getRolePermission($roles): array
    {
        $rules = $this->getRoleRules($roles);
        // 超级管理员
        if (in_array('*', $rules)) {
            return ['*'];
        }
        $nodeEntity = new AdminNode();
        $keys = $nodeEntity->whereIn('id', $rules)->column('key');
        $permissions = [];
        foreach ($keys as $key) {
            if (!$key = $nodeEntity->getNodePermission($key)) {
                continue;
            }
            $code = str_replace('/', '.', trim($key, '/'));
            $permissions[] = $code;
        }
        return $permissions;
    }

    /**
     * 获取角色全部权限
     * @param $roleIds
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getRoleRules($roleIds): array
    {
        $rules = $roleIds ? $this->whereIn('id', $roleIds)->column('rules') : [];
        $result = [];
        foreach ($rules as $item) {
            if (!$item) {
                continue;
            }
            $result = array_merge($result, explode(',', $item));
        }
        return $result;
    }
}