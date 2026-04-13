<?php
/**
 * Admin应用用户操作
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 22:00
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminRole;
use app\admin\entity\AdminUser;
use app\admin\validate\AdminUserValidate;
use Cdyun\PhpTool\Crypto;

#[NodeGroup(Type::NodeGroup['common'])]
class AccountController extends AdminBaseController
{
    /**
     * 登录操作
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function login(): void
    {
        $data = [
            'username' => $this->request->param('username'),
            'password' => $this->request->param('password'),
            'captcha' => $this->request->param('captcha'),
        ];
        validate_data($data, AdminUserValidate::class, 'login');
        $user = new AdminUser();
        $user->doLogin($data);
        success();
    }


    /**
     * 登出操作
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function logout(): void
    {
        session('admin', null);
        success();
    }

    /**
     * 获取用户信息
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function info(): void
    {
        $admin = admin();
        if (!$admin) {
            error('登录状态失效');
        }
        $entity = new AdminRole();
        $rules = $entity->getRoleRules($admin['roles']);
        $info = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'nickname' => $admin['nickname'],
            'avatar' => $admin['avatar'],
            'email' => $admin['email'],
            'mobile' => $admin['mobile'],
            'isSuperAdmin' => in_array('*', $rules),
            'login_at' => $admin['login_at'],
            'login_count' => $admin['login_count'],
            'last_ip' => $admin['last_ip'],
        ];
        success($info);
    }

    /**
     * 账户设置
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch();
    }

    /**
     * 更新账户设置
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function update(): void
    {
        $data = [
            'nickname' => $this->request->post('nickname'),
            'email' => $this->request->post('email'),
            'mobile' => $this->request->post('mobile'),
        ];
        validate_data($data, AdminUserValidate::class, 'update');

        $entity = new AdminUser();
        $user = $entity->where('id', admin('id'))->find();
        $user->nickname = $data['nickname'];
        $user->email = $data['email'];
        $user->mobile = $data['mobile'];
        $user->save();
        refresh_admin_session(true);
        success();
    }

    /**
     * 修改密码
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function password(): void
    {
        $data = [
            'old_password' => $this->request->post('old_password'),
            'password' => $this->request->post('password'),
            'password_confirm' => $this->request->post('password_confirm'),
        ];
        validate_data($data, AdminUserValidate::class, 'password');

        $entity = new AdminUser();
        $user = $entity->where('id',admin('id') )->find();
        if (!Crypto::passwordVerify($data['old_password'], $user->password)) {
            error('旧密码错误');
        }
        if ($data['password'] === $data['old_password']) {
            error('新密码不能与旧密码一致');
        }
        $user->password = Crypto::passwordHash($data['password']);
        $user->save();
        session('admin', null);
        success();
    }
}