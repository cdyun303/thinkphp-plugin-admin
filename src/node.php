<?php
/**
 * Admin后台菜单节点
 * @author cdyun(121625706@qq.com)
 */

return [
    [
        'title' => '系统管理',
        'key' => 'sys',
        'icon' => 'layui-icon-set',
        'weight' => 1000,
        'type' => 0,
        'children' => [
            [
                'title' => '账户管理',
                'key' => 'Thinkphp\\Admin\\controller\\core\\AdminController',
                'href' => '/admin/core/admin/index',
                'type' => 1,
                'weight' => 1000,
            ],
            [
                'title' => '角色管理',
                'key' => 'Thinkphp\\Admin\\controller\\core\\RoleController',
                'href' => '/admin/core/role/index',
                'type' => 1,
                'weight' => 900,
            ],
            [
                'title' => '节点管理',
                'key' => 'Thinkphp\\Admin\\controller\\core\\NodeController',
                'href' => '/admin/core/node/index',
                'type' => 1,
                'weight' => 800,
            ],
            [
                'title' => '行为日志',
                'key' => 'Thinkphp\\Admin\\controller\\core\\LogsController',
                'href' => '/admin/core/logs/index',
                'type' => 1,
                'weight' => 100,
            ],
        ]
    ],
    [
        'title' => '通用设置',
        'key' => 'common',
        'icon' => 'layui-icon-component',
        'weight' => 900,
        'type' => 0,
        'children' => [
            [
                'title' => '平台设置',
                'key' => 'Thinkphp\\Admin\\controller\\core\\ConfigController',
                'href' => '/admin/core/config/index',
                'type' => 1,
                'weight' => 1000,
            ],
            [
                'title' => '个人资料',
                'key' => 'Thinkphp\\Admin\\controller\\core\\AccountController',
                'href' => '/admin/core/account/index',
                'type' => 1,
                'weight' => 900,
            ],
            [
                'title' => '附件管理',
                'key' => 'Thinkphp\\Admin\\controller\\core\\UploadController',
                'href' => '/admin/core/upload/index',
                'type' => 1,
                'weight' => 800,
            ],
            [
                'title' => '邮件设置',
                'key' => 'Thinkphp\\Admin\\controller\\core\\MailController',
                'href' => '/admin/core/mail/index',
                'type' => 1,
                'weight' => 700,
            ],
            [
                'title' => '短信设置',
                'key' => 'Thinkphp\\Admin\\controller\\core\\SmsController',
                'href' => '/admin/core/sms/index',
                'type' => 1,
                'weight' => 600,
            ],
            [
                'title' => '数据字典',
                'key' => 'Thinkphp\\Admin\\controller\\core\\DictController',
                'href' => '/admin/core/dict/index',
                'type' => 1,
                'weight' => 500,
            ],
        ]
    ],
    [
        'title' => '开发辅助',
        'key' => 'dev',
        'icon' => 'layui-icon-fonts-code',
        'weight' => 800,
        'type' => 0,
        'children' => [
            [
                'title' => '数据库',
                'key' => 'Thinkphp\\Admin\\controller\\core\\TableController',
                'href' => '/admin/core/table/index',
                'type' => 1,
                'weight' => 1000,
            ],
            [
                'title' => '表单构建',
                'key' => 'Thinkphp\\Admin\\controller\\core\\FormController',
                'href' => '/admin/core/form/index',
                'type' => 1,
                'weight' => 900,
            ],
            [
                'title' => '插件管理',
                'key' => 'app\\admin\\controller\\core\\PluginController',
                'href' => '/admin/core/plugin/index',
                'type' => 1,
                'weight' => 800,
            ],
        ]
    ],
    [
        'title' => '页面提示',
        'key' => 'tips',
        'icon' => 'layui-icon-template-1',
        'weight' => 100,
        'type' => 0,
        'children' => [
            [
                'title' => '成功',
                'key' => 'app\\admin\\controller\\core\\PageController@pageSuccess',
                'type' => 1,
                'href' => '/admin/core/page/page_success',
                'weight' => 1000
            ],
            [
                'title' => '失败',
                'key' => 'app\\admin\\controller\\core\\PageController@pageError',
                'type' => 1,
                'href' => '/admin/core/page/page_error',
                'weight' => 900
            ],
            [
                'title' => '403',
                'key' => 'app\\admin\\controller\\core\\PageController@page403',
                'type' => 1,
                'href' => '/admin/core/page/page_403',
                'weight' => 800
            ],
            [
                'title' => '404',
                'key' => 'app\\admin\\controller\\core\\PageController@page404',
                'type' => 1,
                'href' => '/admin/core/page/page_404',
                'weight' => 700
            ],
            [
                'title' => '500',
                'key' => 'app\\admin\\controller\\core\\PageController@page500',
                'type' => 1,
                'href' => '/admin/core/page/page_500',
                'weight' => 600
            ]
        ]
    ],
];
