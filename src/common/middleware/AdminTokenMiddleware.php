<?php
/**
 * Admin应用管理员登录中间件
 * @author cdyun(121625706@qq.com)
 * @date 2026/4/10 00:07
 */

declare (strict_types=1);

namespace app\admin\common\middleware;

class AdminTokenMiddleware
{
    public function handle($request, \Closure $next)
    {
        // 验证登录
        $admin = session('admin');
        if (!$admin) {
            session('admin', null);
            return miss(401, '登录已过期，请重新登录', 'admin/miss/index');
        }
        $request->aid = $admin['id'];
        $request->admin = $admin;
        return $next($request);
    }
}