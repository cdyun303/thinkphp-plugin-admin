<?php
/**
 * Admin应用用户登录日志监听器
 * @author cdyun(121625706@qq.com)
 * @date 2026/4/13 00:42
 */

declare (strict_types=1);

namespace app\admin\common\listener;

use app\admin\entity\AdminLog;
use think\facade\Log;

class AdminLoginLog
{
    /**
     * 操作日志
     * @param $event - 数据
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function handle($event = null): void
    {
        $data = [
            'admin_id' => $event['id'],
            'module' => app('http')->getName(),
            'controller' => strtolower(request()->controller()),
            'action' => request()->action(),
            'method' => request()->method(),
            'url' => request()->url(), // 获取完成URL
            'param' => json_encode(['账号密码忽略']),
            'title' => '用户登录 - '.$event['username'],
            'status' => 1,
            'ip' => get_ip(),
            'user_agent' => request()->header('user-agent') ?: '1',
            'browser' => browser(request()->header('user-agent')),
            'os' => os(request()->header('user-agent')),
            'content' => '-',
        ];

        try {
            $entity = new AdminLog();
            $entity->create($data);
        } catch (\Throwable $e) {
            Log::error(json_encode([
                'message' => '记录用户日志失败 ',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE));
        }
    }

}