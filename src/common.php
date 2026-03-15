<?php
// 公共函数文件
use think\facade\Route;

/**
 * 验证码
 * @param $config
 * @return string
 * @author cdyun(121625706@qq.com)
 */
function admin_captcha_src($config = null): string
{
    return Route::buildUrl('/admin/captcha' . ($config ? "/{$config}" : ''));
}