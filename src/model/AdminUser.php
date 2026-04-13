<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 15:15
 */

declare (strict_types=1);

namespace app\admin\model;

use support\base\BaseModel;
use think\model\relation\BelongsToMany;

class AdminUser extends BaseModel
{

    protected $name = 'admin_user';

    /**
     * 用户关联角色
     * @return BelongsToMany
     * @author cdyun(121625706@qq.com)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('AdminRole', 'admin_user_role', 'role_id', 'admin_id');
    }
}