<?php
/**
 * AdminBaseController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 19:02
 */

declare (strict_types=1);

namespace app\admin\controller;

use app\admin\entity\AdminRole;
use app\admin\entity\AdminUser;
use support\base\BaseController;

class AdminBaseController extends BaseController
{
    /**
     * 数据限制
     * null 不做限制，任何管理员都可以查看该表的所有数据
     * auth 管理员能看到自己以及自己的子管理员插入的数据
     * personal 管理员只能看到自己插入的数据
     * @var string
     */
    protected string $dataLimit = '';

    /**
     * 数据限制字段
     * @var string
     */
    protected string $dataLimitField = 'admin_id';

    /**
     * 构建数据限制查询条件
     * @param $where
     * @param string $primaryKey
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    protected function queryWhereLimit(&$where, string $primaryKey = 'id'): void
    {
        $entity = new AdminUser();
        $isSuperAdmin = $entity->isSuperAdmin();
        // 按照数据限制字段返回数据
        if (!$isSuperAdmin) {
            if ($this->dataLimit === 'personal') {
                $where[$this->dataLimitField] = admin('id');
            } elseif ($this->dataLimit === 'auth') {
                if (!isset($where[$primaryKey]) || $this->dataLimitField != $primaryKey) {
                    $roleEntity = new AdminRole();
                    $where[$this->dataLimitField] = ['in', $roleEntity->getScopeAdminIds(true)];
                }
            }
        }
    }
}