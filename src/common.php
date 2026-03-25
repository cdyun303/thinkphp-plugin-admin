<?php
// 公共函数文件
use app\admin\entity\AdminUser;
use think\facade\Route;

/**
 * 验证码
 * @param $config
 * @return string
 * @author cdyun(121625706@qq.com)
 */
function admin_captcha_src($config = null): string
{
    return Route::buildUrl('/admin/captcha' . ($config ? "/{$config}" : ''));
}

/**
 * 当前管理员
 * @param $fields - 字段
 * @return array|mixed|null
 * @author cdyun(121625706@qq.com)
 */
function admin($fields = null): mixed
{
    refresh_admin_session();
    if (!$admin = session('admin')) {
        return null;
    }
    if ($fields === null) {
        return $admin;
    }
    if (is_array($fields)) {
        $results = [];
        foreach ($fields as $field) {
            $results[$field] = $admin[$field] ?? null;
        }
        return $results;
    }
    return $admin[$fields] ?? null;
}

/**
 * 刷新当前管理员session
 * @param bool $force - 强制刷新
 * @return void|null
 * @author cdyun(121625706@qq.com)
 */
function refresh_admin_session(bool $force = false)
{
    $sAdmin = session('admin');
    if (!$sAdmin) {
        return null;
    }

    $adminId = $sAdmin['id'];
    $timeNow = time();
    // session在2秒内不刷新
    $sessionTtl = 2;
    $sessionTime = !empty($sAdmin['session_time']) ? $sAdmin['session_time'] : 0;
    if (!$force && $timeNow - $sessionTime < $sessionTtl) {
        return null;
    }
    $entity = new AdminUser();
    try {
        $user = $entity->where('id', $adminId)->find();
        if (!$user) {
            session('admin', null);
            return null;
        }
    } catch (\Exception $e) {
        return null;
    }
    $user = $user->toArray();
    $user['password'] = md5($user['password']);
    $sAdmin['password'] = $sAdmin['password'] ?? '';
    if ($user['password'] != $sAdmin['password']) {
        session('admin', null);
        return null;
    }
    // 账户被禁用
    if ($user['status'] != 1) {
        session('admin', null);
        return;
    }
    $user['roles'] = $entity->getRoleIds($user['id']);
    $user['session_time'] = $timeNow;
    $entity->setAdminSession($user);
}