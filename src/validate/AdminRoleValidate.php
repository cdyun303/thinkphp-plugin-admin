<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 14:46
 */

declare (strict_types=1);

namespace app\admin\validate;

use support\base\BaseValidate;

class AdminRoleValidate extends BaseValidate
{
    protected $rule = [
        'id' => 'require',
        'pid' => 'require',
        'title' => 'require|max:32',
        'name' => 'require|alias_rg|max:80',
        'remark' => 'max:64',
    ];
    protected $message = [
        'id.require' => '角色ID必须填写',
        'pid.require' => '角色父级必须选择',
        'title.require' => '角色名必须填写',
        'name.require' => '角色英文标识',
        'name.alias_rg' => '角色英文标识由字母开头，小写字母数字下划线构成',
        'remark.max' => '备注不能超过64个字符',
    ];
    protected $scene = [
        'create' => ['pid', 'title', 'name', 'remark'],
        'edit' => ['id', 'pid', 'title', 'name', 'remark'],
    ];

}