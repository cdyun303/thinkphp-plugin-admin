<?php
/**
 * 数据库
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:30
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\exception\AdminException;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use support\util\DbUtil;
use think\facade\Db;

#[NodeGroup(Type::NodeGroup['dev'])]
#[NodeItem(['title' => '数据库', 'href' => '/admin/core/table/index', 'weight' => 1000])]
class TableController extends AdminBaseController
{
    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch();
    }

    /**
     * 查询表
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function list(): void
    {
        $options = [
            'table_name' => $this->request->get('table_name', ''),
            'field' => $this->request->get('field', 'TABLE_NAME'),
            'order' => $this->request->get('order', 'asc'),
            'limit' => (int)$this->request->get('limit', 10),
            'page' => (int)$this->request->get('page', 1)
        ];

        try {
            $tables = DbUtil::listTables($options);
            $total = DbUtil::countTables($options);
        } catch (\Exception $e) {
            throw new AdminException($e->getMessage());
        }
        paginate($tables, $total);
    }

    /**
     * 新增
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function create()
    {
        if ($this->request->isGet()) {
            return $this->fetch();
        }
        $data = $this->request->post();
        try {
            // 表名称验证
            $data['table'] = DbUtil::verify($data['table'], 'alphaDash');
            $prefix = DbUtil::getDbConfig('prefix');
            $tableName = str_starts_with($data['table'], $prefix) ? $data['table'] : $prefix . $data['table'];
            $tableName = strtolower($tableName);
            if (Db::table('information_schema.TABLES')->where('TABLE_NAME', $tableName)->count()) {
                throw new \Exception('表已存在');
            }

            $options = [
                'table_comment' => $data['table_comment'] ?: '',
                'table_charset' => $data['table_charset'] ?: '',
                'table_collation' => $data['table_collation'] ?: '',
                'table_engine' => $data['table_engine'] ?: '',
            ];
            $sql = DbUtil::buildTableSql($tableName, $data['columns'], $data['keys'], $options);
            Db::query($sql);

        } catch (\Exception $e) {
            error($e->getMessage());
        }
        success();
    }

    /**
     * 编辑
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function edit()
    {
        if ($this->request->isGet()) {
            $this->assign('table', $this->request->get('table'));
            return $this->fetch();
        }
        $data = $this->request->post();
        try {
            $prefix = DbUtil::getDbConfig('prefix');
            $data['old_table'] = DbUtil::verify($data['old_table'], 'alphaDash');
            $oldTableName = str_starts_with($data['old_table'], $prefix) ? $data['old_table'] : $prefix . $data['old_table'];
            $oldTableName = strtolower($oldTableName);

            $data['table'] = DbUtil::verify($data['table'], 'alphaDash');
            $tableName = str_starts_with($data['table'], $prefix) ? $data['table'] : $prefix . $data['table'];
            $tableName = strtolower($tableName);
            if ($oldTableName != $tableName && Db::table('information_schema.TABLES')->where('TABLE_NAME', $tableName)->count()) {
                throw new \Exception('表已存在');
            }

            $options = [
                'table_name' => $tableName,
                'table_comment' => $data['table_comment'] ?: '',
                'table_charset' => $data['table_charset'] ?: '',
                'table_collation' => $data['table_collation'] ?: '',
                'table_engine' => $data['table_engine'] ?: '',
            ];
            DbUtil::modifyTable($oldTableName, $data['columns'] ?? [], $data['keys'] ?? [], $options);

        } catch (\Exception $e) {
            error($e->getMessage());
        }
        success();
    }

    /**
     * 表摘要
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function schema(): void
    {
        $table = $this->request->get('table');
        try {
            $data = DbUtil::getSchema($table);
        } catch (\Exception $e) {
            error($e->getMessage());
        }
        success([
            'table' => $data['table'],
            'columns' => array_values($data['columns']),
            'keys' => array_values($data['keys']),
        ]);
    }

    /**
     * 删除
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function delete(): void
    {
        $tables = $this->request->post('tables');
        if (!$tables) {
            error('请选择要删除的表');
        }
        $prefix = DbUtil::getDbConfig('prefix');

        $notAllowDelete = [
            $prefix . 'admin_user',
            $prefix . 'admin_role',
            $prefix . 'admin_user_role',
            $prefix . 'admin_node',
            $prefix . 'admin_log',
            $prefix . 'option',
            $prefix . 'user',
            $prefix . 'upload'
        ];
        $tables = is_array($tables) ? $tables : [$tables];
        if ($found = array_intersect($tables, $notAllowDelete)) {
            error(implode(',', $found) . '不允许删除');
        }

        try {
            DbUtil::dropTable($tables);
        } catch (\Exception $e) {
            error($e->getMessage());
        }
        success();
    }
}