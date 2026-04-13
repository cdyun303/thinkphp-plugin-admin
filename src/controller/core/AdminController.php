<?php
/**
 * 账户管理
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/22 16:45
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminUser;
use app\admin\validate\AdminUserValidate;

#[NodeGroup(Type::NodeGroup['sys'])]
#[NodeItem(['title' => '账户管理', 'href' => '/admin/core/admin/index', 'weight' => 1000])]
class AdminController extends AdminBaseController
{
    /**
     * 开启auth数据限制
     * @var string
     */
    protected string $dataLimit = 'auth';
    /**
     * 以id为数据限制字段
     * @var string
     */
    protected string $dataLimitField = 'id';

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
     * 查询
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function list(): void
    {
        $entity = new AdminUser();
        [$where, $format, $limit, $field, $order] = $entity->doSearchSelect($this->request);
        $this->queryWhereLimit($where);
        $query = $entity->doQueryWhere($where, $field, $order);

        if ($format == 'select') {
            $result = $query->select()->toArray();
            success($result);
        }
        $query = $query->with('roles')->paginate($limit);
        $items = $query->items();
        $login_admin_id = admin('id');
        foreach ($items as $index => $item) {
            $items[$index]['roles'] = $item['roles']->column('id');
            $items[$index]['show_toolbar'] = $item['id'] != $login_admin_id;
        }
        paginate($items, $query->total());
    }

    /**
     * 添加
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function create(): string
    {
        if ($this->request->isPost()) {
            $params = input('param.');
            validate_data($params, AdminUserValidate::class, 'create');

            $params['roles'] = !empty($params['roles']) ? explode(',', $params['roles']) : [];
            if (empty($params['roles'])) {
                error('请选择角色');
            }
            $adminEntity = new AdminUser();
            $adminEntity->doSaveAdminUser($params);
            success();
        }
        return $this->fetch();
    }

    /**
     * 编辑
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function edit(): string
    {
        if ($this->request->isPost()) {
            $params = input('param.');
            validate_data($params, AdminUserValidate::class, 'edit');
            // 不能禁用自己
            if (isset($data['status']) && $data['status'] == 0 && $params['id'] == admin('id')) {
                error('不能禁用自己');
            }

            $params['roles'] = !empty($params['roles']) ? explode(',', $params['roles']) : [];
            if (empty($params['roles'])) {
                error('请选择角色');
            }

            $adminEntity = new AdminUser();
            $adminEntity->doSaveAdminUser($params);
            success();
        }
        return $this->fetch();
    }

    /**
     * 更新
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function update(): void
    {
        $id = $this->request->post('id');
        $status = $this->request->post('status');

        // 不能禁用自己
        if ($status == 0 && $id == admin('id')) {
            error('不能禁用自己');
        }

        $adminEntity = new AdminUser();
        $adminEntity->update(['status' => $status], ['id' => $id]);
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
        $id = $this->request->post('id', []);
        if (empty($id)) {
            error('参数错误');
        }
        $entity = new AdminUser();
        $id = is_array($id) ? $id : [$id];
        $entity->doDeleteAdminUser($id);
        success();
    }
}