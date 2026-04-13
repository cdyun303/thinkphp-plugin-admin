<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 14:46
 */

declare (strict_types=1);

namespace app\admin\validate;

use support\base\BaseValidate;

class AdminNodeValidate extends BaseValidate
{
    protected $regex = [
        //标识，由字母数字下划线反斜杠等特殊符号构成
        'key_rg' => '/^[A-Za-z\d!@#$%&)(_\\\\]+$/'
    ];
    protected $rule = [
        'id' => 'require',
        'app_name' => 'require',
        'title' => 'require|max:32',
        'key' => 'require|key_rg|max:200',
        'type' => 'in:0,1,2',
        'weight' => 'number',
    ];
    protected $message = [
        'id.require' => 'ID必须',
        'app_name.require' => '应用名必须选择',
        'title.require' => '标题必须填写',
        'title.max' => '标题不能超过32个字符',
        'key.require' => '标识必须填写',
        'key.key_rg' => '标识由字母数字特殊符号构成',
        'key.max' => '标识不能超过200个字符',
        'type.in' => '节点类型错误',
        'weight.max' => '节点排序格式错误',
    ];
    protected $scene = [
        'create' => ['app_name','title', 'key', 'type', 'weight'],
        'edit' => ['id', 'app_name','title', 'key', 'type', 'weight'],
    ];

}