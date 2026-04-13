<?php
/**
 * 短信设置
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/23 02:29
 */

declare (strict_types=1);

namespace app\admin\controller\core;

use app\admin\common\annotation\NodeGroup;
use app\admin\common\annotation\NodeItem;
use app\admin\common\Type;
use app\admin\controller\AdminBaseController;
use app\admin\entity\Option;

#[NodeGroup(Type::NodeGroup['common'])]
#[NodeItem(['title' => '短信设置', 'href' => '/admin/core/sms/index', 'weight' => 600])]
class SmsController extends AdminBaseController
{
    protected string $smsConfig = 'config_sms';

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
     * 获取短信配置
     * @return mixed
     * @author cdyun(121625706@qq.com)
     */
    public function get(): mixed
    {
        $entity = new Option();
        return $entity->getOptionConfig($this->smsConfig);
    }

    /**
     * 编辑
     * @return void
     * @author cdyun(121625706@qq.com)
     */
    #[NodeItem]
    public function edit(): void
    {
        $sms = $this->request->post('sms');
        if (!is_array($sms) || empty($sms)) {
            error('短信服务配置不能为空');
        }
        if (empty($sms['default']) || !is_string($sms['default'])){
            error('默认短信服务不能为空');
        }
        if (empty($sms[$sms['default']]) || !is_array($sms[$sms['default']])){
            error('默认短信服务不存在');
        }
        $entity = new Option();
        $entity->where('name', $this->smsConfig)->update([
            'value' => json_encode($sms)
        ]);
        success();
    }

}