<?php
/**
 * AdminRole.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 23:52
 */

declare (strict_types=1);

namespace Thinkphp\Admin\model;

use support\base\BaseModel;
use think\model\concern\SoftDelete;

class AdminRole extends BaseModel
{
    use SoftDelete;

    protected $name = 'admin_role';

}