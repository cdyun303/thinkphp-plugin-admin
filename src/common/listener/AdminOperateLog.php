<?php
/**
 * Admin应用用户操作日志监听器
 * @author cdyun(121625706@qq.com)
 * @date 2026/4/12 23:34
 */

declare (strict_types=1);

namespace app\admin\common\listener;

use app\admin\entity\AdminLog;
use app\admin\entity\AdminNode;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Log;

class AdminOperateLog
{
    /**
     * 操作日志
     * @param null $response - 响应内容
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author cdyun(121625706@qq.com)
     */
    public function handle($response = null): void
    {
        $data = [
            'admin_id' => request()->aid ?: 0,
            'module' => app('http')->getName(),
            'controller' => strtolower(request()->controller()),
            'action' => request()->action(),
            'method' => request()->method(),
            'url' => request()->url(), // 获取完成URL
            'param' => json_encode(request()->param() ? request()->param() : []),
            'title' => $this->getTitle(),
            'status' => 1,
            'ip' => get_ip(),
            'user_agent' => request()->header('user-agent') ?: '1',
            'browser' => browser(request()->header('user-agent')),
            'os' => os(request()->header('user-agent')),
        ];

        if ($response) {
            // 限制记录的响应内容，避免过大
            $_response = $response->getContent();
            $data['content'] = mb_substr($_response, 0, 3000, 'utf-8');
        }

        try {
            $dao = new AdminLog();
            $dao->create($data);
        } catch (\Throwable $e) {
            Log::error(json_encode([
                'message' => '记录用户日志失败 ',
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 获取标题
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author cdyun(121625706@qq.com)
     */
    public function getTitle(): string
    {
        $controllerLayer = config('route.controller_layer', 'controller');
        $isControllerSuffix = config('route.controller_suffix');
        $module = app('http')->getName();
        $controller = request()->controller();
        $controller = $isControllerSuffix ? $controller . 'Controller' : $controller;
        $action = strtolower(request()->action());
        $key = 'app.' . $module . '.' . $controllerLayer . '.' . $controller;
        $key = str_replace('.', '\\', $key);

        $title = [];
        $entity = new AdminNode();
        $rs = $entity->where('key', $key)->field('title, key')->find();
        if ($rs){
            $title[] = $rs['title'];
        }

        $key = $key . '@' . $action;
        $rs = $entity->where('key', $key)->field('title, key')->find();
        if ($rs){
            $title[] = $rs['title'];
        }
        return implode(' - ', $title);
    }

}