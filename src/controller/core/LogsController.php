<?php
/**
 * LogsController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:19
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class LogsController extends  AdminBaseController
{
    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch('logs/index');
    }

}