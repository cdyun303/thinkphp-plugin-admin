<?php
/**
 * InstallController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 23:38
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;
use app\admin\validate\AdminUserValidate;
use Cdyun\PhpTool\Crypto;
use Cdyun\PhpTool\Dir;
use think\facade\Session;

class InstallController extends AdminBaseController
{
    /**
     * 设置数据库
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function step1()
    {
        $isInstall = base_path('admin') . 'install.lock';
        clearstatcache();
        if (is_file($isInstall)) {
            error('管理后台已经安装！如需重新安装，请删除该文件再试！');
        }

        $type = $this->request->post('type');
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $database = $this->request->post('database');
        $prefix = $this->request->post('prefix');
        $hostname = $this->request->post('hostname');
        $hostport = (int)$this->request->post('hostport') ?: 3306;
        $overwrite = $this->request->post('overwrite');
        try {
            $db = $this->getPdo($hostname, $username, $password, $hostport);
            $smt = $db->query("show databases like '$database'");
            if (empty($smt->fetchAll())) {
                $db->exec("create database $database");
            }
            $db->exec("use $database");
            $smt = $db->query("show tables");
            $tables = $smt->fetchAll();
        } catch (\Throwable $e) {
            if (stripos($e->getMessage(), 'Access denied for user')) {
                error('数据库用户名或密码错误！');
            }
            if (stripos($e->getMessage(), 'Connection refused')) {
                error('Connection refused. 请确认数据库IP端口是否正确，数据库已经启动！');
            }
            if (stripos($e->getMessage(), 'timed out')) {
                error('数据库连接超时，请确认数据库IP端口是否正确，安全组及防火墙已经放行端口！');
            }
            error($e->getMessage());
        }

        $tables_to_install = [
            $prefix . 'admin_user',
            $prefix . 'admin_role',
            $prefix . 'admin_user_role',
            $prefix . 'admin_node',
            $prefix . 'option',
            $prefix . 'user',
            $prefix . 'upload',
        ];
        $tables_exist = [];
        foreach ($tables as $table) {
            $tables_exist[] = current($table);
        }
        $tables_conflict = array_intersect($tables_to_install, $tables_exist);
        if (!$overwrite) {
            if ($tables_conflict) {
                error('以下表' . implode(',', $tables_conflict) . '已经存在，如需覆盖请选择强制覆盖');
            }
        } else {
            foreach ($tables_conflict as $table) {
                $db->exec("DROP TABLE `$table`");
            }
        }

        $sql_file = base_path('admin') . 'install.sql';
        if (!is_file($sql_file)) {
            error($sql_file . ',数据库SQL文件不存在');
        }

        $sql_query = file_get_contents($sql_file);
        $sql_query = $this->removeComments($sql_query);
        $sql_query = str_replace('wa20260315_', $prefix, $sql_query);
        $sql_query = $this->splitSqlFile($sql_query, ';');
        foreach ($sql_query as $sql) {
            $sql = trim($sql);
            if (!empty($sql)) {
                $db->exec($sql);
            }
        }
        // 导入菜单
        $menus = Dir::getFileContent(base_path('admin'), 'node');

        // 安装过程中没有数据库配置，无法使用api\Menu::import()方法
        $this->importMenu($prefix . 'admin_node', $menus, $db);

        $config_content = <<<EOF
<?php
// 数据库配置
return [
    // 默认使用的数据库连接配置
    'default'         => '$type',

    // 自定义时间查询规则
    'time_query_rule' => [],

    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,

    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',

    // 时间字段配置 配置格式：create_time,update_time
    'datetime_field'  => '',

    // 数据库连接配置信息
    'connections'     => [
        '$type' => [
            // 数据库类型
            'type'            => '$type',
            // 服务器地址
            'hostname'        => '$hostname',
            // 数据库名
            'database'        => '$database',
            // 用户名
            'username'        => '$username',
            // 密码
            'password'        => '$password',
            // 端口
            'hostport'        => '$hostport',
            // 数据库连接参数
            'params'          => [],
            // 数据库编码
            'charset'         => env('$type.charset', 'utf8mb4'),
            // 数据库表前缀
            'prefix'          => '$prefix',

            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'          => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'     => false,
            // 读写分离后 主服务器数量
            'master_num'      => 1,
            // 指定从服务器序号
            'slave_no'        => '',
            // 是否严格检查字段是否存在
            'fields_strict'   => true,
            // 是否需要断线重连
            'break_reconnect' => false,
            // 监听SQL
            'trigger_sql'     => env('app_debug', true),
            // 开启字段缓存
            'fields_cache'    => false,
        ],

        // 更多的数据库配置信息
    ],
];
EOF;

        // 写入数据库配置文件
        $configDir = base_path('admin/config');
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configFile = $configDir . 'database.php';
        file_put_contents($configFile, $config_content);

        // 如果复制失败，直接使用生成的内容
        if (!is_file($configFile) || !is_writable($configFile)) {
            @chmod($configFile, 0644);
        }

        // 写入安装记录标记
        file_put_contents($isInstall, '管理后台已经安装！如需重新安装，请删除该文件再试！');

        success();
    }

    /**
     * 获取pdo连接
     * @param $host
     * @param $username
     * @param $password
     * @param $port
     * @param $database
     * @return \PDO
     */
    protected function getPdo($host, $username, $password, $port, $database = null): \PDO
    {
        $dsn = "mysql:host=$host;port=$port;";
        if ($database) {
            $dsn .= "dbname=$database";
        }
        $params = [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8mb4",
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_TIMEOUT => 5,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        return new \PDO($dsn, $username, $password, $params);
    }

    /**
     * 去除sql文件中的注释
     * @param $sql
     * @return string
     */
    protected function removeComments($sql): string
    {
        return preg_replace("/(\n--[^\n]*)/", "", $sql);
    }

    /**
     * 分割sql文件
     * @param $sql
     * @param $delimiter
     * @return array
     */
    function splitSqlFile($sql, $delimiter): array
    {
        $tokens = explode($delimiter, $sql);
        $output = array();
        $matches = array();
        $token_count = count($tokens);
        for ($i = 0; $i < $token_count; $i++) {
            if (($i != ($token_count - 1)) || (strlen($tokens[$i]) > 0)) {
                $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
                $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
                $unescaped_quotes = $total_quotes - $escaped_quotes;

                if (($unescaped_quotes % 2) == 0) {
                    $output[] = $tokens[$i];
                    $tokens[$i] = "";
                } else {
                    $temp = $tokens[$i] . $delimiter;
                    $tokens[$i] = "";

                    $complete_stmt = false;
                    for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
                        $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                        $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
                        $unescaped_quotes = $total_quotes - $escaped_quotes;
                        if (($unescaped_quotes % 2) == 1) {
                            $output[] = $temp . $tokens[$j];
                            $tokens[$j] = "";
                            $temp = "";
                            $complete_stmt = true;
                            $i = $j;
                        } else {
                            $temp .= $tokens[$j] . $delimiter;
                            $tokens[$j] = "";
                        }

                    }
                }
            }
        }

        return $output;
    }

    /**
     * 导入菜单
     * @param string $table - 表名
     * @param array $menu_tree - 菜单树
     * @param \PDO $pdo - 数据库连接
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    protected function importMenu(string $table, array $menu_tree, \PDO $pdo)
    {
        if (is_numeric(key($menu_tree)) && !isset($menu_tree['key'])) {
            foreach ($menu_tree as $item) {
                $this->importMenu($table, $item, $pdo);
            }
            return;
        }
        $children = $menu_tree['children'] ?? [];
        unset($menu_tree['children']);
        $smt = $pdo->prepare("select * from " . $table . " where `key`=:key limit 1");
        $smt->execute(['key' => $menu_tree['key']]);
        $old_menu = $smt->fetch();
        if ($old_menu) {
            $pid = $old_menu['id'];
            $params = [
                'title' => $menu_tree['title'],
                'icon' => $menu_tree['icon'] ?? '',
                'key' => $menu_tree['key'],
            ];
            $sql = "update " . $table . " set title=:title, icon=:icon where `key`=:key";
            $smt = $pdo->prepare($sql);
            $smt->execute($params);
        } else {
            $pid = $this->addMenu($table, $menu_tree, $pdo);
        }
        foreach ($children as $menu) {
            $menu['pid'] = $pid;
            $this->importMenu($table, $menu, $pdo);
        }
    }

    /**
     * 添加菜单
     * @param string $table - 表名
     * @param array $menu - 菜单数据
     * @param \PDO $pdo - 数据库连接
     * @return false|string
     * @author cdyun(121625706@qq.com)
     */
    protected function addMenu(string $table, array $menu, \PDO $pdo)
    {
        $allow_columns = ['title', 'key', 'icon', 'href', 'pid', 'weight', 'type'];
        $data = [];
        foreach ($allow_columns as $column) {
            if (isset($menu[$column])) {
                $data[$column] = $menu[$column];
            }
        }
        $time = date('Y-m-d H:i:s');
        $data['create_at'] = $data['update_at'] = $time;
        $values = [];
        foreach ($data as $k => $v) {
            $values[] = ":$k";
        }
        $columns = array_keys($data);
        foreach ($columns as $k => $column) {
            $columns[$k] = "`$column`";
        }
        $sql = "insert into " . $table . " (" . implode(',', $columns) . ") values (" . implode(',', $values) . ")";
        $smt = $pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $smt->bindValue($key, $value);
        }
        $smt->execute();
        return $pdo->lastInsertId();
    }

    /**
     * 步骤2创建管理员
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function step2()
    {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        validate_data(['username' => $username, 'password' => $password,], AdminUserValidate::class, 'step');
        $password_confirm = $this->request->post('password_confirm');
        if ($password != $password_confirm) {
            error('两次密码不一致');
        }
        $isInstall = base_path('admin') . 'install.lock';
        if (!is_file($isInstall)) {
            error('请先完成第一步数据库配置');
        }
        $default = config('database.default', 'mysql');
        $connection = config('database.connections.' . $default);
        $pdo = $this->getPdo($connection['hostname'], $connection['username'], $connection['password'], $connection['hostport'], $connection['database']);

        $adminTable = $connection['prefix'] . 'admin_user';
        if ($pdo->query('select * from `' . $adminTable . '`')->fetchAll()) {
            error('管理后台已经安装完毕，无法通过此页面创建管理员');
        }

        $smt = $pdo->prepare("insert into `" . $adminTable . "` (`username`, `password`, `nickname`, `create_at`, `update_at`) values (:username, :password, :nickname, :create_at, :update_at)");
        $time = date('Y-m-d H:i:s');
        $data = [
            'username' => $username,
            'password' => Crypto::passwordHash($password),
            'nickname' => '超级管理员',
            'create_at' => $time,
            'update_at' => $time
        ];
        foreach ($data as $key => $value) {
            $smt->bindValue($key, $value);
        }
        $smt->execute();
        $admin_id = $pdo->lastInsertId();

        $adminRoleTable = $connection['prefix'] . 'admin_user_role';
        $smt = $pdo->prepare("insert into `" . $adminRoleTable . "` (`role_id`, `admin_id`) values (:role_id, :admin_id)");
        $smt->bindValue('role_id', 1);
        $smt->bindValue('admin_id', $admin_id);
        $smt->execute();

        Session::clearFlashData();
        success();
    }
}