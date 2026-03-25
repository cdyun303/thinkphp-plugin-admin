<?php
/**
 * PageController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:33
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class PageController extends AdminBaseController
{
    /**
     * 成功页面
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function page_success(): string
    {
        return $this->fetch('page/success');
    }

    /**
     * 错误页面
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function page_error(): string
    {
        return $this->fetch('page/error');
    }

    /**
     * 403页面
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function page_403(): string
    {
        return $this->fetch('page/403');
    }

    /**
     * 404页面
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function page_404(): string
    {
        return $this->fetch('page/404');
    }

    /**
     * 500页面
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function page_500(): string
    {
        return $this->fetch('page/500');
    }
}