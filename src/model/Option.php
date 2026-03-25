<?php
/**
 * Option.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 22:23
 */

declare (strict_types=1);

namespace app\admin\model;

use support\base\BaseModel;
use think\model\concern\SoftDelete;

class Option extends BaseModel
{
    use SoftDelete;

    protected $name = 'option';
}