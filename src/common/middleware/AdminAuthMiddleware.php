<?php
/**
 * Admin应用权限中间件
 * @author cdyun(121625706@qq.com)
 * @date 2026/4/1 01:55
 */

declare (strict_types=1);

namespace app\admin\common\middleware;

use app\admin\entity\AdminRole;

class AdminAuthMiddleware
{
    public function handle($request, \Closure $next)
    {
        // 验证权限
        $module = app('http')->getName();
        $controller = strtolower($request->controller());
        $action = $request->action();
        $code = $module . '.' . $controller . '.' . $action;
        $roles = $request->admin['roles'];
        $roleEntity = new AdminRole();
        $permission = $roleEntity->getRolePermission($roles);
        // 超级管理员或拥有权限
        if (in_array('*', $permission) || in_array($code, $permission)) {
            return $next($request);
        }
        if (strtolower($action) === 'index') {
            foreach ($permission as $item) {
                if (str_starts_with($item, $module . '.' . $controller)) {
                    return $next($request);
                }
            }

        }
        return miss(403, '权限不足', 'admin/miss/index');
    }
}