<?php
// 全局事件定义文件

return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'AdminOperate' => ['app\admin\common\listener\AdminOperateLog'],
        'AdminLogin' => ['app\admin\common\listener\AdminLoginLog'],
    ],

    'subscribe' => [
    ],
];