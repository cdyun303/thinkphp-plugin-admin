<?php
/**
 * InstallController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 23:38
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;

class InstallController extends AdminBaseController
{
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
            $prefix . 'admin_rule',
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
}