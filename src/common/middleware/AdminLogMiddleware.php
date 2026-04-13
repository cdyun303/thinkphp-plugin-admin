<?php
/**
 * Admin应用日志中间件
 * @author cdyun(121625706@qq.com)
 * @date 2026/4/9 23:57
 */

declare (strict_types=1);

namespace app\admin\common\middleware;
class AdminLogMiddleware
{
    public function handle($request, \Closure $next)
    {
        // 日志数据
        $controller = strtolower($request->controller(base:true));
        $method = strtolower($request->method());
        $action = strtolower(request()->action());

        if (
            in_array($method, ['post', 'put', 'delete']) && // 请求方法
            !in_array($action, ['login', 'refresh']) && // 不是登录请求
            !str_contains($controller, 'logs') // 不是日志请求
        ) {
            $response = $next($request);

            event('AdminOperate', $response);
            return $response;
        } else {
            return $next($request);
        }
    }
}