<?php
/**
 * 邮件设置
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:25
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\Option;

#[NodeGroup(Type::NodeGroup['common'])]
#[NodeItem(['title' => '邮件设置', 'href' => '/admin/core/mail/index', 'weight' => 700])]
class MailController extends AdminBaseController
{
    protected string $mailConfig = 'config_mail';
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
     * 获取邮件配置
     * @return mixed
     * @author cdyun(121625706@qq.com)
     */
    public function get(): mixed
    {
        $entity = new Option();
        return $entity->getOptionConfig($this->mailConfig);
    }

    /**
     * 编辑
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function edit(): void
    {
        $mail = $this->request->post('mail');
        if (!is_array($mail) || empty($mail)) {
            error('邮件服务配置不能为空');
        }
        if (empty($mail['default']) || !is_string($mail['default'])){
            error('默认邮件服务不能为空');
        }
        if (empty($mail[$mail['default']]) || !is_array($mail[$mail['default']])){
            error('默认邮件服务不存在');
        }
        $entity = new Option();
        $entity->where('name', $this->mailConfig)->update([
            'value' => json_encode($mail)
        ]);
        success();
    }
}