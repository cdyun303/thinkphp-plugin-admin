<?php
/**
 * 通用常量定义
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/30 01:14
 */

namespace app\admin\common;

class Type
{
    //Admin应用节点分组
    public const NodeGroup = [
        'sys' => [
            'title' => '系统管理',
            'key' => 'sys',
            'icon' => 'layui-icon-set',
            'weight' => 1000
        ],
        'common' => [
            'title' => '通用设置',
            'key' => 'common',
            'icon' => 'layui-icon-component',
            'weight' => 900
        ],
        'dev' => [
            'title' => '开发辅助',
            'key' => 'dev',
            'icon' => 'layui-icon-fonts-code',
            'weight' => 800
        ],
    ];
}