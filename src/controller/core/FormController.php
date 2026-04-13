<?php
/**
 * 表单构建
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:31
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;

#[NodeGroup(Type::NodeGroup['dev'])]
#[NodeItem(['title' => '表单构建', 'href' => '/admin/core/form/index', 'weight' => 900])]
class FormController extends AdminBaseController
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

}