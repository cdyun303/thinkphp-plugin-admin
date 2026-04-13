<?php
/**
 * 错误控制器
 * @author cdyun(121625706@qq.com)
 * @date 2026/4/10 00:26
 */

declare (strict_types=1);

namespace app\admin\controller;

class MissController extends AdminBaseController
{
    /**
     * 错误页面
     * @return string
     * @author cdyun(121625706@qq.com)
     */
    public function index(): string
    {
        $params = input('param.');
        $type = $params['type'] ?? '404';
        $msg = $params['msg'] ?? '未知错误';

        if ($this->request->isAjax()){
            error($msg);
        }
        $this->assign('msg',$msg);
        $this->assign('type',$type);

        //默认页面
        if (!in_array($type,['401','403','404','503'])){
            $type = 'index';
        }
        return $this->fetch("page/{$type}");
    }

}