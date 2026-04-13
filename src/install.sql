
CREATE TABLE IF NOT EXISTS `wa20260315_admin_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `admin_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `role_id` int(11) UNSIGNED NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `role_admin_id` (`role_id`,`admin_id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统用户与角色关系表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `wa20260315_admin_user` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '登录名',
  `nickname` varchar(40) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `avatar` varchar(255) NOT NULL DEFAULT '/static/admin/images/avatar.webp' COMMENT '头像',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `mobile` varchar(16) NOT NULL DEFAULT '' COMMENT '手机',
  `login_count` int(0) NOT NULL DEFAULT '0' COMMENT '登录次数',
  `last_ip` varchar(80) NOT NULL DEFAULT '' COMMENT '登录ip',
  `remark` varchar(128) NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态  1：启用,0：停用',
  `op_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `login_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '登录时间',
  `create_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  `update_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统用户表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `wa20260315_option` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '键',
  `value` longtext NULL COMMENT '值',
  `create_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  `update_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `delete_at` int(0) NOT NULL DEFAULT '0' COMMENT '软删除类型int',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统字典表' ROW_FORMAT = Dynamic;

LOCK TABLES `wa20260315_option` WRITE;
INSERT INTO `wa20260315_option`
VALUES (1, 'config_system',
        '{\"logo\":{\"title\":\"Thinkphp Admin\",\"image\":\"\\/static\\/admin\\/images\\/logo.png\"},\"menu\":{\"data\":\"\\/admin\\/core\\/node\\/get\",\"method\":\"GET\",\"accordion\":true,\"collapse\":false,\"control\":false,\"controlWidth\":500,\"select\":\"0\",\"async\":true},\"tab\":{\"enable\":true,\"keepState\":true,\"preload\":false,\"session\":true,\"max\":\"30\",\"index\":{\"id\":\"0\",\"href\":\"\\/admin\\/core\\/index\\/dashboard\",\"title\":\"\\u4eea\\u8868\\u76d8\"}},\"theme\":{\"defaultColor\":\"2\",\"defaultMenu\":\"light-theme\",\"defaultHeader\":\"light-theme\",\"allowCustom\":true,\"banner\":false},\"colors\":[{\"id\":\"1\",\"color\":\"#16b777\",\"second\":\"#f0f9eb\"},{\"id\":\"2\",\"color\":\"#1890ff\",\"second\":\"#ecf5ff\"},{\"id\":\"3\",\"color\":\"#faad14\",\"second\":\"#fdf6ec\"},{\"id\":\"4\",\"color\":\"#ff4d4f\",\"second\":\"#fef0f0\"},{\"id\":\"5\",\"color\":\"#646cff\",\"second\":\"#ecf5ff\"}],\"other\":{\"keepLoad\":\"500\",\"autoHead\":false,\"footer\":false},\"header\":{\"message\":false}}',
        '2022-12-05 14:49:01', '2022-12-08 20:20:28','0'),
       (2,'config_mail','{\"default\":\"qq\",\"qq\":{\"username\":\"\",\"password\":\"\",\"smtp\":\"\",\"port\":\"\"}}','2026-03-31 12:55:24','2026-03-31 12:55:24',0),
       (3,'config_sms','{\"default\":\"aliyun\",\"aliyun\":{\"accessKeyId\":\"\",\"accessKeySecret\":\"\",\"endpoint\":\"dysmsapi.aliyuncs.com\"}}','2026-03-31 12:55:30','2026-03-31 12:55:30',0),
       (4, 'dict_upload',
        '[{\"value\":\"1\",\"name\":\"图片\"},{\"value\":\"2\",\"name\":\"音视频\"},{\"value\":\"3\",\"name\":\"文档\"},{\"value\":\"4\",\"name\":\"其他\"}]',
        '2022-12-04 16:24:13', '2022-12-04 16:24:13','0'),
       (5, 'dict_sex', '[{\"value\":\"0\",\"name\":\"女\"},{\"value\":\"1\",\"name\":\"男\"}]', '2022-12-04 15:04:40',
        '2022-12-04 15:04:40','0'),
       (6, 'dict_status', '[{\"value\":\"0\",\"name\":\"禁用\"},{\"value\":\"1\",\"name\":\"正常\"}]',
        '2022-12-04 15:05:09', '2022-12-04 15:05:09','0'),
       (7, 'dict_dict_name','[{\"value\":\"dict_name\",\"name\":\"字典名称\"},{\"value\":\"status\",\"name\":\"启禁用状态\"},{\"value\":\"sex\",\"name\":\"性别\"},{\"value\":\"upload\",\"name\":\"附件分类\"}]',
        '2022-08-15 00:00:00', '2022-12-20 19:42:51','0');
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `wa20260315_admin_role` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '角色名标识',
  `rules` text NULL COMMENT '权限',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级',
  `remark` varchar(128) NOT NULL DEFAULT '' COMMENT '备注',
  `op_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `create_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  `update_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统角色表' ROW_FORMAT = Dynamic;

LOCK TABLES `wa20260315_admin_role` WRITE;
INSERT INTO `wa20260315_admin_role` VALUES (1,'超级管理员','admin','*',0,'超级管理员', 'sys', '2022-03-15 09:05:01', '2022-03-15 09:05:01');
UNLOCK TABLES;

CREATE TABLE IF NOT EXISTS `wa20260315_admin_node` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `key` varchar(255) NOT NULL DEFAULT '' COMMENT '标识',
  `pid` int(10) UNSIGNED DEFAULT '0' COMMENT '上级菜单',
  `href` varchar(255) NOT NULL DEFAULT '' COMMENT 'url',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型:1,2,3',
  `is_menu` tinyint(4) NOT NULL DEFAULT '1' COMMENT '菜单显示  1：时,0：否',
  `app_name` varchar(64) NOT NULL DEFAULT '' COMMENT '应用名',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `op_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `create_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  `update_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `is_menu` (`is_menu`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单节点信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `wa20260315_upload` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件',
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员',
  `file_size` int(11) NOT NULL COMMENT '文件大小',
  `mime_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'mime类型',
  `image_width` int(11) DEFAULT NULL COMMENT '图片宽度',
  `image_height` int(11) DEFAULT NULL COMMENT '图片高度',
  `ext` varchar(128) NOT NULL DEFAULT '' COMMENT '扩展名',
  `storage` varchar(255) NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `category` varchar(128) NOT NULL DEFAULT '' COMMENT '类别',
  `create_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  `update_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `admin_id` (`admin_id`),
  KEY `name` (`name`),
  KEY `ext` (`ext`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统附件表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `wa20260315_admin_log` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员',
  `module` varchar(64) NOT NULL DEFAULT '' COMMENT '应用名',
  `controller` varchar(64) NOT NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(64) NOT NULL DEFAULT '' COMMENT '方法',
  `method` varchar(64) NOT NULL DEFAULT '' COMMENT '请求类型',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '请求连接',
  `param` json NULL COMMENT '请求参数',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '标题',
  `content` longtext NULL COMMENT '响应内容',
  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT 'IP',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `browser` varchar(64) NOT NULL DEFAULT '' COMMENT '浏览器',
  `os` varchar(64) NOT NULL DEFAULT '' COMMENT '系统',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态[1正常,0停用]',
  `op_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人',
  `create_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) COMMENT '创建时间',
  `update_at` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  `delete_at` int(11) NOT NULL DEFAULT '0' COMMENT '软删除类型int',
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统日志表' ROW_FORMAT = Dynamic;
