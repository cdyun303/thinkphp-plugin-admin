<?php
/**
 * AdminLog.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 22:23
 */

declare (strict_types=1);

namespace app\admin\model;

use support\base\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\HasOne;

class AdminLog extends BaseModel
{
    use SoftDelete;

    protected $name = 'admin_log';

    /**
     * 关联用户
     * @return HasOne
     * @author cdyun(121625706@qq.com)
     */
    public function user(): HasOne
    {
        return $this->hasOne(AdminUser::class, 'id', 'admin_id')->field('id,username,nickname');
    }
}