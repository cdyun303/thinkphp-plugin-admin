<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 14:46
 */

declare (strict_types=1);

namespace app\admin\validate;

use support\base\BaseValidate;

class AdminUserValidate extends BaseValidate
{
    protected $rule = [
        'username' => 'require|verifyAccount',
        'password' => 'require',
        'captcha' => 'require|captcha',
        'nickname' => 'require|max:12',
        'email' => 'email',
        'mobile' => 'mobile',
        'old_password' => 'require',
        'password_confirm' => 'require|confirm:password',
    ];
    protected $message = [
        'username.require' => '用户名不能为空',
        'username.verifyAccount' => '用户名由字母开头，5-16位字母数字下划线构成或手机号码',
        'password.require' => '密码不能为空',
        'password.pwd_rg' => '密码由6-18位，字母数字特殊符号构成',
        'captcha.require' => '验证码不能为空',
        'captcha.captcha' => '验证码错误',
        'nickname.require' => '昵称不能为空',
        'nickname.max' => '昵称最多12个字符',
        'email.email' => '邮箱格式错误',
        'mobile.mobile' => '手机号码错误',
        'old_password.require' => '原始密码不能为空',
        'password_confirm.require' => '确认密码不能为空',
        'password_confirm.confirm' => '确认密码与新密码不一致',
    ];
    protected $scene = [
        'login' => ['username', 'password', 'captcha'],
        'update' => ['nickname', 'email', 'mobile'],
        'password' => ['old_password', 'password', 'password_confirm'],
    ];

    /**
     * 安装系统时注册账号密码验证
     * @return AdminUserValidate
     * @author cdyun(121625706@qq.com)
     */
    public function sceneStep()
    {
        return $this->only(['username', 'password'])->append('password', 'pwd_rg');
    }

}