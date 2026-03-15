<?php
/**
 * ConfigController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 22:02
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class ConfigController extends AdminBaseController
{

    public function get()
    {
        $config = file_get_contents(root_path('public/static/admin/config'). 'pear.config.json');
        return json_decode($config, true);
    }
}