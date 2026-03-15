<?php
/**
 * admin.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 18:42
 */

use think\facade\Route;

// 首页路由
Route::get('/', 'core/index/index');
// 验证码
Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');

Route::group(function () {
    Route::group('core', function () {
        Route::group('config', function () {
            Route::rule('get', 'get', 'GET');
        })->prefix('core/config/')->completeMatch();
    });
    Route::group('core', function () {
        Route::group('rule', function () {
            Route::rule('get', 'get', 'GET');
        })->prefix('core/rule/')->completeMatch();
    });
    Route::group('core', function () {
        Route::group('install', function () {
            Route::rule('step1', 'step1', 'POST');
        })->prefix('core/install/')->completeMatch();
    });

    Route::group(':w', function () {
        Route::group(':v', function () {
            Route::rule('index', 'index', 'GET');
        })->prefix(':w/:v/')->completeMatch();
    });

    Route::miss(function () {
        return '404 Not Found!';
    });
});

// 放在最后
Route::auto();