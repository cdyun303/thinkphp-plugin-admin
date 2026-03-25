<?php
/**
 * AdminController.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/22 16:45
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminUser;

class AdminController extends AdminBaseController
{
    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        return $this->fetch("admin/index");
    }

    /**
     * 查询
     * @return void
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function list(): void
    {
        $entity = new AdminUser();
        [$where, $format, $limit, $field, $order] = $entity->doSearchSelect($this->request);

        $query = $entity->doQueryWhere($where, $field, $order);

        if ($format == 'select') {
            $result = $query->select()->toArray();
            success($result);
        }
        $query = $query->with('roles')->paginate($limit);
        $items = $query->items();
        $login_admin_id = admin('id');
        foreach ($items as $index => $item) {
            $items[$index]['roles'] =  $item['roles']->column('title');
            $items[$index]['show_toolbar'] = $item['id'] != $login_admin_id;
        }
        paginate($items, $query->total());
    }

    /**
     * 添加
     * @return string
     * @throws \Exception
     * @author cdyun(121625706@qq.com)
     */
    public function create()
    {
        if (request()->isAjax()) {
            success();
        }
        return $this->fetch("admin/create");
    }
}