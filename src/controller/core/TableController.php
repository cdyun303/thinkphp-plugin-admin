<?php
/**
 * TableController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:30
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class TableController extends AdminBaseController
{
    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch('table/index');
    }

}