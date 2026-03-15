<?php
/**
 * AccountController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 22:00
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class AccountController extends AdminBaseController
{

    public function login()
    {
        success();
    }

    public function logout()
    {
        success();
    }

    public function info()
    {
        error();
    }

}