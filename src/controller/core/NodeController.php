<?php
/**
 * 节点管理
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/20 23:38
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Node;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminNode;
use app\admin\entity\AdminRole;
use app\admin\validate\AdminNodeValidate;
use Cdyun\PhpTool\Arr;
use Cdyun\PhpTool\Dir;

#[NodeGroup(Type::NodeGroup['sys'])]
#[NodeItem(['title' => '节点管理', 'href' => '/admin/core/node/index', 'weight' => 800])]
class NodeController extends AdminBaseController
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
     * 权限码
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function permission(): void
    {
        $roleEntity = new AdminRole();
        success($roleEntity->getRolePermission(admin('roles')));
    }

    /**
     * 查询
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function list()
    {
        $nodeEntity = new AdminNode();
        [$where, $format, $limit, $field, $order] = $nodeEntity->doSearchSelect($this->request);

        // 允许通过type=0,1格式传递菜单类型
        $types = $this->request->get('type');
        if ($types && is_string($types)) {
            $where['type'] = ['in', explode(',', $types)];
        }
        $query = $nodeEntity->doQueryWhere($where, 'weight', 'desc');

        if ($format == 'select') {
            $data = $query->select();
            $result = [];
            foreach ($data as $item) {
                $result[] = [
                    'id' => $item['id'],
                    'pid' => $item['pid'],
                    'name' => $item['title'],
                    'value' => $item['id'],
                ];
            }
            success(Arr::toTree($result, 0, 'id', 'pid', 'children'));
        }
        $query = $query->paginate($limit);
        $items = $query->items();
        paginate($items, $query->total());
    }

    /**
     * 获取角色菜单节点
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function get(): void
    {
        $roleEntity = new AdminRole();
        $rules = $roleEntity->getRoleRules(admin('roles'));
        $types = $this->request->get('type', '1,2');
        $types = is_string($types) ? explode(',', $types) : [1, 2];
        $nodeEntity = new AdminNode();
        $nodes = $nodeEntity->order('weight', 'desc')->select()->toArray();

        $fmtNodes = [];
        foreach ($nodes as $item) {
            $item['pid'] = (int)$item['pid'];
            $item['name'] = $item['title'];
            $item['value'] = $item['id'];
            $item['icon'] = $item['icon'] ? "layui-icon {$item['icon']}" : '';
            $fmtNodes[] = $item;
        }
        $nodeTree = Arr::toTree($fmtNodes, 0, 'id', 'pid');
        // 超级管理员权限为 *
        if (!in_array('*', $rules)) {
            $nodeEntity->removeNotContain($nodeTree, 'id', $rules);
        }
        $nodeEntity->removeNotContain($nodeTree, 'type', $types);
        $menus = $nodeEntity->empty_filter($nodeTree);
        success($menus);
    }

    /**
     * 新增
     * @return string|void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function create()
    {
        if ($this->request->isGet()) {
            return $this->fetch();
        }

        $params = $this->request->post();
        validate_data($params, AdminNodeValidate::class, 'create');
        if (empty($params['type'])) {
            $params['type'] = strpos($params['key'], '\\') ? 2 : 1;
        }
        $params['key'] = str_replace('\\\\', '\\', $params['key']);

        $nodeEntity = new AdminNode();
        if ($nodeEntity->where('key', $params['key'])->find()) {
            error('菜单标识 ' . $params['key'] . ' 已经存在');
        }
        $params['pid'] = empty($params['pid']) ? 0 : $params['pid'];
        $params['is_menu'] = empty($params['is_menu']) ? 0 : 1;
        $nodeEntity->create($params);
        success();
    }

    /**
     * 编辑
     * @return string|void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function edit()
    {
        if ($this->request->isGet()) {
            return $this->fetch();
        }

        $params = $this->request->post();
        validate_data($params, AdminNodeValidate::class, 'edit');

        $nodeEntity = new AdminNode();
        if (!$row = $nodeEntity->find($params['id'])) {
            error('记录不存在');
        }
        if (isset($params['pid'])) {
            $params['pid'] = $params['pid'] ?: 0;
            if ($params['pid'] == $row['id']) {
                error('不能将自己设置为上级菜单');
            }
        }
        if (isset($params['key'])) {
            $params['key'] = str_replace('\\\\', '\\', $params['key']);
        }
        $params['is_menu'] = empty($params['is_menu']) ? 0 : 1;
        $nodeEntity->update($params, ['id' => $params['id']]);
        success();
    }

    /**
     * 删除
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function delete(): void
    {
        $ids = $this->request->post('id', []);
        if (empty($ids)) {
            error('参数错误');
        }

        $nodeEntity = new AdminNode();
        $deleteIds = $childIds = is_array($ids) ? $ids : [$ids];
        while ($childIds) {
            $childIds = $nodeEntity->whereIn('pid', $childIds)->column('id');
            $deleteIds = array_merge($deleteIds, $childIds);
        }
        $nodeEntity->destroy($deleteIds);
        success();
    }

    /**
     * 获取应用列表
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    public function list_app(): void
    {
        $result = [];
        $appNames = Dir::scanFolder(base_path());
        foreach ($appNames as $app) {
            $result[] = ['name' => $app, 'value' => $app];
        }
        success($result);
    }

    /**
     * 导入权限
     * @return void
     * @throws \ReflectionException
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function import(): void
    {
        $appNames = Dir::scanFolder(base_path());
        foreach ($appNames as $app) {
            Node::import($app);
        }
        success();
    }
}