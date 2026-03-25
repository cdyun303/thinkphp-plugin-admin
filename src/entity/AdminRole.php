<?php
/**
 * AdminRole.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 23:53
 */

declare (strict_types=1);

namespace app\admin\entity;

use app\admin\exception\AdminException;
use Cdyun\PhpTool\Arr;
use support\base\BaseEntity;

class AdminRole extends BaseEntity
{
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
            $result = array_merge($rules, explode(',', $item));
        }
        return $result;
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
            $roles = $this->field('id,pid,name,title,status')->select()->toArray();
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
}