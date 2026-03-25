<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 15:19
 */

declare (strict_types=1);

namespace Thinkphp\Admin\entity;

use Thinkphp\Admin\exception\AdminException;
use Cdyun\PhpTool\Crypto;
use support\base\BaseEntity;
use Thinkphp\Admin\entity\AdminRole;
use function app\admin\entity\error;
use function app\admin\entity\get_ip;
use function app\admin\entity\runtime_path;
use function app\admin\entity\session;

class AdminUser extends BaseEntity
{
    /**
     *  登录
     * @param $data
     * @return true
     * @author cdyun(121625706@qq.com)
     */
    public function doLogin($data): bool
    {
        // 检查登录频率限制
        $this->checkLoginLimit($data['username']);
        // 检查用户名密码
        try {
            $user = $this->where('username', $data['username'])->find();
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        if (!$user || !Crypto::passwordVerify($data['password'], $user->password)) {
            throw new AdminException('账户不存在或密码错误');
        }
        if ($user->status != 1) {
            throw new AdminException('当前账户暂时无法登录');
        }
        $user->login_at = date('Y-m-d H:i:s');
        $user->login_count += 1;
        $user->last_ip = get_ip();
        $user->save();

        // 移除登录限制
        $this->removeLoginLimit($data['username']);

        // 设置Session，登陆时不设置session_time或设置为0，否则会导致获取不到角色Ids
        $user->password = md5($user->password);
        $this->setAdminSession($user->toArray());
        return true;
    }

    /**
     * 获取角色ID
     * @param $uid
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getRoleIds($uid): array
    {
        return (new \Thinkphp\Admin\model\AdminUserRole)->where('admin_id', $uid)->column('role_id');
    }

    /**
     * 检查登录频率限制
     * @param $username - 用户名
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    protected function checkLoginLimit($username): void
    {
        $limit_log_path = runtime_path() . '/login';
        if (!is_dir($limit_log_path)) {
            mkdir($limit_log_path, 0777, true);
        }
        $limit_file = $limit_log_path . '/' . md5($username) . '.limit';
        $time = date('YmdH') . ceil(date('i') / 5);
        $limit_info = [];
        if (is_file($limit_file)) {
            $json_str = file_get_contents($limit_file);
            $limit_info = json_decode($json_str, true);
        }

        if (!$limit_info || $limit_info['time'] != $time) {
            $limit_info = [
                'username' => $username,
                'count' => 0,
                'time' => $time
            ];
        }
        $limit_info['count']++;
        file_put_contents($limit_file, json_encode($limit_info));
        if ($limit_info['count'] >= 5) {
            error('登录失败次数过多，请5分钟后再试');
        }
    }

    /**
     * 解除登录频率限制
     * @param $username
     * @return void
     */
    protected function removeLoginLimit($username): void
    {
        $limit_log_path = runtime_path() . '/login';
        $limit_file = $limit_log_path . '/' . md5($username) . '.limit';
        if (is_file($limit_file)) {
            unlink($limit_file);
        }
    }

    /**
     * 设置Admin Session
     * @param array $user
     * @return mixed
     * @author cdyun(121625706@qq.com)
     */
    public function setAdminSession(array $user): mixed
    {
        return session('admin', [
            'id' => $user['id'],
            'username' => $user['username'],
            'password' => $user['password'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'login_at' => $user['login_at'],
            'login_count' => $user['login_count'],
            'last_ip' => $user['last_ip'],
            'roles' => $user['roles'] ?? [],
            'session_time' => $user['session_time'] ?? 0,
        ]);
    }

    /**
     * 是否是超级管理员
     * @param int $adminId - 管理员ID
     * @return bool
     * @author cdyun(121625706@qq.com)
     */
    public function isSuperAdmin(int $adminId = 0): bool
    {
        if (!$adminId) {
            if (!$roles = admin('roles')) {
                return false;
            }
        } else {
            $roles = $this->getRoleIds($adminId);
        }
        $dao = new AdminRole();
        $rules = $dao->whereIn('id', $roles)->column('rules');
        return $rules && in_array('*', $rules);
    }
}