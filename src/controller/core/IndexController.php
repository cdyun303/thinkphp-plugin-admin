<?php
/**
 * IndexController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/12 02:06
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class IndexController extends AdminBaseController
{
    public function index()
    {
        clearstatcache();
        if (!is_file(base_path('admin') . 'install.lock')) {
            $config = config('database');
            $db_default = $config['default'] ?? 'mysql';
            $mysql = $config['connections'][$db_default] ?? [];
            $db_config = [
                'type' => $db_default,
                'username' => $mysql['username'] ?? 'root',
                'password' => $mysql['password'] ?? '',
                'database' => $mysql['database'] ?? 'tp_admin',
                'hostname' => $mysql['hostname'] ?? '127.0.0.1',
                'hostport' => $mysql['hostport'] ?? '3306',
                'prefix' => $mysql['prefix'] ?? 'sys_',
            ];
            return view('index/install', ['db' => $db_config]);
        }
        $admin = false;
        if (!$admin) {
            return view('admin_account/login');
        }
        return view('index/index');
    }
}