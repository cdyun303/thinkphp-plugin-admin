<?php
/**
 * AdminUser.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 15:19
 */

declare (strict_types=1);

namespace app\admin\entity;

use app\admin\common\exception\AdminException;
use app\admin\model\AdminUserRole;
use Cdyun\PhpTool\Crypto;
use support\base\BaseEntity;
use think\facade\Db;

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

        event('AdminLogin', $user);
        return true;
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
     * 保存用户
     * @param array $data
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function doSaveAdminUser(array $data): void
    {
        // 启动事务
        Db::startTrans();
        try {
            if (!is_array($data['roles'])) {
                throw new AdminException('用户角色格式错误');
            }

            $aurDao = new AdminUserRole();
            $roleEntity = new AdminRole();
            $scopeRoleIds = $roleEntity->getScopeRoleIds();

            $isSuperAdmin = $this->isSuperAdmin();
            if (isset($data['id'])) {
                $roleIds = $this->getRoleIds(admin('id'));
                if (!$isSuperAdmin && !array_intersect($roleIds, $scopeRoleIds)) {
                    throw new AdminException('无权限更改该记录');
                }
                if (!$isSuperAdmin && array_diff($data['roles'], $scopeRoleIds)) {
                    throw new AdminException('角色超出权限范围');
                }

                // 密码处理
                if (isset($data['password']) && $data['password'] !== '') {
                    $data['password'] = Crypto::passwordHash($data['password']);
                } else {
                    unset($data['password']); // 如果密码为空则不更新密码字段
                }
                $this->update($data, ['id' => $data['id']]);

                $deleteIds = array_diff($roleIds, $data['roles']);
                $rs = $aurDao->whereIn('role_id', $deleteIds)->where('admin_id', $data['id'])->delete();
                if ($rs === false) {
                    throw new AdminException('用户保存角色失败');
                }
                $uerId = $data['id'];
            } else {
                if ($this->where('username', $data['username'])->find()) {
                    throw new AdminException('用户名已存在');
                }
                if (!$isSuperAdmin && array_diff($data['roles'], $scopeRoleIds)) {
                    throw new AdminException('角色超出权限范围');
                }

                $data['password'] = Crypto::passwordHash($data['password']);
                $rs = $this->create($data);
                $uerId = $rs->id;
            }

            $aurDao->saveAll(array_map(function ($roleId) use ($uerId) {
                return ['admin_id' => $uerId, 'role_id' => $roleId];
            }, $data['roles']));
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new AdminException($e->getMessage());
        }
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

    /**
     * 获取角色ID
     * @param $uid
     * @return array
     * @author cdyun(121625706@qq.com)
     */
    public function getRoleIds($uid): array
    {
        return (new AdminUserRole)->where('admin_id', $uid)->column('role_id');
    }

    /**
     * 删除用户
     * @param $ids
     * @return true
     * @author cdyun(121625706@qq.com)
     */
    public function doDeleteAdminUser($ids): bool
    {
        // 启动事务
        Db::startTrans();
        try {
            if (in_array(admin('id'), $ids)) {
                throw new AdminException('不能删除自己');
            }

            $aurDao = new AdminUserRole();
            $roleEntity = new AdminRole();
            $scopeAdminIds = $roleEntity->getScopeAdminIds();

            $isSuperAdmin = $this->isSuperAdmin();

            if (!$isSuperAdmin && array_diff($ids, $scopeAdminIds)) {
                throw new AdminException('无数据权限');
            }

            $this->destroy(
                function ($query) use ($ids) {
                    $query->whereIn('id', $ids);
                }
            );
            $aurDao->destroy(
                function ($query) use ($ids) {
                    $query->whereIn('admin_id', $ids);
                }
            );
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw new AdminException($e->getMessage());
        }
        return true;
    }

}