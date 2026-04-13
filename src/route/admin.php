<?php
/**
 * admin.php
 * @author cdyun(121625706@qq.com)
 * @date 2026/3/14 18:42
 */

use app\admin\common\middleware\AdminAuthMiddleware;
use app\admin\common\middleware\AdminLogMiddleware;
use app\admin\common\middleware\AdminTokenMiddleware;
use think\facade\Route;

// 首页路由
Route::get('/', 'core/index/index');
// 验证码
Route::get('captcha/[:config]', '\\think\\captcha\\CaptchaController@index');

Route::group(function () {
    $middleware = [
        AdminTokenMiddleware::class, // 登录
        AdminAuthMiddleware::class, // 权限
        AdminLogMiddleware::class, // 日志
    ];
    Route::group('core', function () use ($middleware) {
        //  安装
        Route::group('install', function () {
            Route::rule('step1', 'step1', 'POST');
            Route::rule('step2', 'step2', 'POST');
        })->prefix('core/install/')->completeMatch()->withoutMiddleware([AdminTokenMiddleware::class]);
        //  登录
        Route::group('account', function () {
            Route::rule('login', 'login', 'POST')->withoutMiddleware([AdminTokenMiddleware::class]);
            Route::rule('logout', 'logout', 'GET');
            Route::rule('info', 'info', 'GET')->withoutMiddleware([AdminTokenMiddleware::class]);
            Route::rule('index', 'index', 'GET')->withoutMiddleware([AdminTokenMiddleware::class]);
            Route::rule('password', 'password', 'POST');
            Route::rule('update', 'update', 'POST');
        })->prefix('core/account/')->completeMatch();
        //  配置
        Route::group('config', function () {
            Route::rule('get', 'get', 'GET')->withoutMiddleware([AdminTokenMiddleware::class]);
        })->prefix('core/config/')->completeMatch();
        //  后台主页
        Route::group('index', function () {
            Route::rule('dashboard', 'dashboard', 'GET');
        })->prefix('core/index/')->completeMatch();
        //  提示页模板展示
        Route::group('page', function () {
            Route::rule('page_success', 'page_success', 'GET');
            Route::rule('page_error', 'page_error', 'GET');
            Route::rule('page_403', 'page_403', 'GET');
            Route::rule('page_404', 'page_404', 'GET');
            Route::rule('page_500', 'page_500', 'GET');
        })->prefix('core/page/')
            ->completeMatch()
            ->withoutMiddleware([AdminTokenMiddleware::class]);
        //  节点菜单
        Route::group('node', function () {
            Route::rule('get', 'get', 'GET')->withoutMiddleware([AdminTokenMiddleware::class]);
            Route::rule('permission', 'permission', 'GET');
            Route::rule('list_app', 'list_app', 'GET');
            Route::rule('import', 'import', 'POST')->middleware([AdminAuthMiddleware::class, AdminLogMiddleware::class]);
        })->prefix('core/node/')->completeMatch();
        //  角色
        Route::group('role', function () {
            Route::rule('rules', 'rules', 'GET');
        })->prefix('core/role/')
            ->completeMatch()
            ->middleware([AdminAuthMiddleware::class, AdminLogMiddleware::class]);
        //  附件
        Route::group('upload', function () {
            Route::rule('attachment', 'attachment', 'GET');
            Route::rule('move_upload', 'move_upload', 'POST');
        })->prefix('core/upload/')
            ->completeMatch()
            ->middleware([AdminAuthMiddleware::class, AdminLogMiddleware::class]);
        //  数据表
        Route::group('table', function () {
            Route::rule('schema', 'schema', 'GET');
        })->prefix('core/table/')
            ->completeMatch()
            ->middleware([AdminAuthMiddleware::class, AdminLogMiddleware::class]);
    })->middleware([AdminTokenMiddleware::class]);
    // 通用路由
    Route::group(':w', function () use ($middleware) {
        Route::group(':v', function () use ($middleware) {
            Route::rule('get', 'get', 'GET')->withoutMiddleware([AdminAuthMiddleware::class]);
            Route::rule('get/:name', 'get', 'GET')->withoutMiddleware([AdminAuthMiddleware::class]);
            Route::rule('index', 'index', 'GET');
            Route::rule('list', 'list', 'GET');
            Route::rule('detail', 'detail', 'GET');
            Route::rule('create', 'create', 'GET|POST');
            Route::rule('edit', 'edit', 'GET|POST');
            Route::rule('update', 'update', 'POST');
            Route::rule('delete', 'delete', 'POST');
        })->prefix(':w/:v/')->completeMatch()->middleware($middleware);
    });
    // 错误页面路由
    Route::get('miss/index', 'miss/index');
    // Miss路由
    Route::miss(function (\think\Request $request) {
        if ($request->isAjax()) {
            error('未知路由访问');
        }
        return miss(404, '未知路由访问', 'admin/miss/index');
    });
});

// 放在最后
Route::auto();