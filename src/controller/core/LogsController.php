<?php
/**
 * 行为日志
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:19
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\AdminLog;
use think\db\exception\DbException;

#[NodeGroup(Type::NodeGroup['sys'])]
#[NodeItem(['title' => '行为日志', 'href' => '/admin/core/logs/index'])]
class LogsController extends AdminBaseController
{
    /**
     * 浏览
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 查询
     * @return void
     * @throws DbException
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function list()
    {
        $entity = new AdminLog();
        [$where, $format, $limit, $field, $order] = $entity->doSearchSelect($this->request);

        $query = $entity->doQueryWhere($where, $field, $order)->with('user')->paginate($limit);
        $items = $query->items();
        paginate($items, $query->total());
    }

    /**
     * 详情
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function detail(): string
    {
        return $this->fetch();
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
        $entity = new AdminLog();
        $ids = is_array($ids) ? $ids : [$ids];
        $entity->destroy(
            function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            }
        );
        success();

    }
}