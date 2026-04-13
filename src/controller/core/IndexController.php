<?php
/**
 *  主页
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/12 02:06
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminUser;
use think\facade\Db;

class IndexController extends AdminBaseController
{
    /**
     * 后台主页
     * @return string
     * @throws \Exception
     */
    public function index(): string
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
            $this->assign([
                'db' => $db_config
            ]);
            return $this->fetch('core/index/install');
        }
        $admin = admin();
        if (!$admin) {
            return $this->fetch('core/account/login');
        }
        return $this->fetch('core/index/index');
    }

    /**
     * 仪表板
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function dashboard(): string
    {
        $userEntity = new AdminUser();
        // 今日新增用户数
        $today_user_count = $userEntity->where('create_at', '>', date('Y-m-d') . ' 00:00:00')->count();
        // 7天内新增用户数
        $day7_user_count = $userEntity->where('create_at', '>', date('Y-m-d H:i:s', time() - 7 * 24 * 60 * 60))->count();
        // 30天内新增用户数
        $day30_user_count = $userEntity->where('create_at', '>', date('Y-m-d H:i:s', time() - 30 * 24 * 60 * 60))->count();
        // 总用户数
        $user_count = $userEntity->count();
        // mysql版本
        $version = Db::query('select VERSION() as version');
        $mysql_version = $version[0]['version'] ?? 'unknown';

        $day7_detail = [];
        $now = time();
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', $now - 24 * 60 * 60 * $i);
            $day7_detail[substr($date, 5)] = $userEntity->where('create_at', '>', "$date 00:00:00")
                ->where('create_at', '<', "$date 23:59:59")->count();
        }

        $this->assign([
            'today_user_count' => $today_user_count,
            'day7_user_count' => $day7_user_count,
            'day30_user_count' => $day30_user_count,
            'user_count' => $user_count,
            'php_version' => PHP_VERSION,
            'thinkphp_version' => $this->getPackageVersion('topthink/framework'),
            'framework_version' => $this->getPackageVersion('cdyun/thinkphp-framework'),
            'admin_version' => $this->getPackageVersion('cdyun/thinkphp-plugin-admin'),
            'mysql_version' => $mysql_version,
            'os' => PHP_OS,
            'day7_detail' => array_reverse($day7_detail),
        ]);

        return $this->fetch('core/index/dashboard');
    }

    /**
     * 获取某个composer包的版本
     * @param string $package
     * @return string
     */
    protected function getPackageVersion(string $package): string
    {
        $installed_php = root_path('vendor/composer') . 'installed.php';
        if (is_file($installed_php)) {
            $packages = include $installed_php;
        }
        return substr($packages['versions'][$package]['version'] ?? 'unknown  ', 0, -2);
    }
}