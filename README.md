# Thinkphp8.0项目的Thinkphp Admin中台应用插件

> Thinkphp Admin 中台应用插件。它提供了一套完整的项目结构和常用功能，帮助开发者快速构建高性能的PHP中台应用。

## 快速安装

```bash
composer require cdyun/thinkphp-plugin-admin
```

## 使用说明

1. **安装基础框架**

```bash
composer create-project cdyun/thinkphp-framework
```

2. **安装 Thinkphp Admin 中台应用插件**

```bash
composer require cdyun/thinkphp-plugin-admin
```

## 完整项目目录结构

```
tp8/                                      部署目录
├── app                                   多应用目录
│   ├── admin                             Thinkphp Admin 中台应用插件
│   │   ├── common                        Admin应用提供的服务接口类：注解类、异常处理类、事件监听器、中间件等
│   │   ├── config                        Admin应用配置目录
│   │   ├── controller                    控制器目录
│   │   │   ├── core                      Core模块
│   │   │   ├── ...                       其他模块
│   │   │   └── AdminBaseController.php   Admin应用基础控制器
│   │   ├── entity                        对应实体模型目录
│   │   ├── model                         模型目录
│   │   ├── route                         路由目录
│   │   │   └── admin.php                 路由（注意配置多模块路由）
│   │   ├── validate                      验证器目录
│   │   ├── view                          视图目录
│   │   ├── common.php                    Admin应用公共函数文件
│   │   ├── event.php                     Admin应用事件配置
│   │   ├── install.sql                   Admin应用数据库安装SQL
│   │   ├── middleware                    Admin应用中间件配置
│   │   └── ...                           其他目录或文件
│   │
│   ├── common.php                        全局公共函数文件
│   ├── event.php                         全局事件定义文件
│   ├── middleware.php                    全局中间件定义文件
│   ├── provider.php                      服务提供器配置
│   └── service.php                       系统服务定义文件
│
├── config                                全局配置目录
│   ├── app.php                           应用配置
│   ├── cache.php                         缓存配置
│   ├── cdyun.php                         cdyun扩展插件配置
│   ├── console.php                       控制台配置
│   ├── cookie.php                        Cookie配置
│   ├── database.php                      数据库配置
│   ├── filesystem.php                    文件系统配置
│   ├── lang.php                          多语言配置
│   ├── log.php                           日志配置
│   ├── middleware.php                    中间件配置
│   ├── route.php                         URL和路由配置
│   ├── session.php                       Session配置
│   ├── swagger.php                       Swagger配置（Swagger扩展）
│   ├── trace.php                         Trace配置
│   └── view.php                          视图配置
│
├── public                                静态资源目录
│  ├─static                               Thinkphp Admin 静态资源插件
│  │   ├── admin
│  │   └── lib
│  │
│  ├─index.php                            入口文件
│  ├─router.php                           快速测试文件
│  └─.htaccess                            用于apache的重写
│
├── runtime                               应用的运行时目录，需要可写权限
├── vendor                                composer安装的第三方类库目录
├── support                               全局类库目录
│   ├── base                              基础类目录
│   │   ├── BaseController.php            基础控制器类
│   │   ├── BaseModel.php                 基础模型类
│   │   └── BaseValidate.php              基础验证类
│   │
│   ├── exception                         异常类目录
│   │   └── AppException.php              自定义异常类
│   │  
│   ├── middleware                        中间件类目录
│   │   └── BrowseCheckMiddleware.php     浏览器类型中间件
│   │  
│   ├── listener                          事件监听类目录
│   │  
│   ├── ...                               其他全局类库目录
│   │  
│   ├── AppService.php                    应用服务类文件
│   ├── ExceptionHandle.php               异常处理类文件
│   └── Request.php                       请求类文件
│
├── .example.env                          环境变量示例文件
├── fix_perms.sh                          一键修改项目文件夹及文件权限脚本
├── LICENSE                               MIT开源协议文件
├── README.md                             README.md
├── composer.json                         项目依赖配置文件
└── think.php                             命令行入口文件
```
## 使用注意

- ✅ Admin 中台应用插件支持多模块和多应用。
- ✅ 使用多应用时，需要配置对应的应用路由和应用 session 功能。
- ✅ 在Admin应用中提供了服务接口类：注解类、异常处理类、事件监听器、中间件等，用于应用节点加载、登录状态检测、权限验证、日志记录

## 许可证

MIT License
