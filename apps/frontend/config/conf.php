<?php

/**
 * 库调用路径
 */
define('LIB_DIR', realpath(APP_DIR . '/libs/'));

/**
 * 上传文件路径
 */
//define('UPLOAD_DIR', realpath('../web_root/up/'));

/**
 * 上传文件目录url前缀
 */
//define('UPLOAD_URL', '/up');

/**
 * 日志路径
 */
define('LOG_DIR', realpath(APP_DIR . '/logs/'));

/**
 * 网站验证码
 */
define('APP_SEC_CODE', '2881a101d21632c921dbca8c531c48c23');

/**
 * 静态文件路径
 */
define('STATIC_URL', '/static');

/**
 * 调试/开发模式
 * 
 * 0: 生产环境
 * 1: 开发环境
 */
define('DEBUG_MODE', 1);

//require LIB_DIR . '/funcs.php'; //调用全局函数
require LIB_DIR . '/Conf.php'; //配置类

/**
 * 普通用户登录session名称
 * 
 */
define('SESS_USER', 'user');

/**
 * 
 */
define('SESS_CHECK', 'check'); 