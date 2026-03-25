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
        //  安装
        Route::group('install', function () {
            Route::rule('step1', 'step1', 'POST');
            Route::rule('step2', 'step2', 'POST');
        })->prefix('core/install/')->completeMatch();
        //  登录
        Route::group('account', function () {
            Route::rule('login', 'login', 'POST');
            Route::rule('logout', 'logout', 'GET');
            Route::rule('info', 'info', 'GET');
            Route::rule('index', 'index', 'GET');
            Route::rule('password', 'password', 'POST');
            Route::rule('update', 'update', 'POST');
        })->prefix('core/account/')->completeMatch();
        //  配置
        Route::group('config', function () {
            Route::rule('get', 'get', 'GET');
            Route::rule('edit', 'edit', 'POST');
        })->prefix('core/config/')->completeMatch();
        //  节点菜单
        Route::group('node', function () {
            Route::rule('get', 'get', 'GET');
            Route::rule('permission', 'permission', 'GET');
        })->prefix('core/node/')->completeMatch();
        //  节点菜单
        Route::group('index', function () {
            Route::rule('dashboard', 'dashboard', 'GET');
        })->prefix('core/index/')->completeMatch();
        //  字典
        Route::group('dict', function () {
            Route::rule('get/:name', 'get', 'GET');
            Route::rule('index', 'index', 'GET');
            Route::rule('list', 'list', 'GET');
            Route::rule('create', 'create', 'GET|POST');
            Route::rule('edit', 'edit', 'GET|POST');
            Route::rule('delete', 'delete', 'POST');
        })->prefix('core/dict/')->completeMatch();

    });


//    Route::group(':w', function () {
//        Route::group(':v', function () {
//            Route::rule('index', 'index', 'GET');
//        })->prefix(':w/:v/')->completeMatch();
//    });
//    Route::miss(function () {
//        return view('page/404');
//    });
});

// 放在最后
Route::auto();