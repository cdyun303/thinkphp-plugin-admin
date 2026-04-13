<?php
/**
 * 附件上传
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:24
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\Upload;

#[NodeGroup(Type::NodeGroup['common'])]
#[NodeItem(['title' => '附件管理', 'href' => '/admin/core/upload/index', 'weight' => 800])]
class UploadController extends AdminBaseController
{
    /**
     * 只返回当前管理员数据
     * @var string
     */
    protected string $dataLimit = 'personal';

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
     * 浏览附件
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function attachment(): string
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
        $entity = new Upload();
        [$where, $format, $limit, $field, $order] = $entity->doSearchSelect($this->request);
        $this->queryWhereLimit($where);
        if (!empty($where['ext']) && is_string($where['ext'])) {
            $where['ext'] = ['in', explode(',', $where['ext'])];
        }
        if (!empty($where['name']) && is_string($where['name'])) {
            $where['name'] = ['like', "%{$where['name']}%"];
        }
        $query = $entity->doQueryWhere($where, $field, $order);

        if ($format == 'select') {
            $result = $query->select()->toArray();
            success($result);
        }
        $query = $query->paginate($limit);
        $items = $query->items();
        foreach ($items as $key=> $item){
            $items[$key]['preview'] = app_sign_url($item['url']);
        }
        paginate($items, $query->total());

    }

    /**
     * 新增
     * @return string|void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function create()
    {
        if ($this->request->isGet()) {
            return $this->fetch();
        }
        $params = input('param.');
        $params['file'] = $this->request->file('file');
        $entity = new Upload();
        $result = $entity->doMoveUpload($params);
        success('上传成功', $result);
    }

    /**
     * 上传
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function move_upload(): void
    {
        $params = input('param.');
        $params['file'] = $this->request->file('file');
        $entity = new Upload();
        $result = $entity->doMoveUpload($params);
        success('上传成功', $result);
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
        $entity = new Upload();
        $ids = is_array($ids) ? $ids : [$ids];
        foreach ($ids as  $id) {
            $entity->doDeleteFile($id);
        }
        success();
    }
}