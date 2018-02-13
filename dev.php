#!/usr/bin/env php
<?php

// CLI应用入口文件(本地开发环境)

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//TRANCE ID
define('TRANCE_ID', microtime(true));
//环境变量
define('RUNTIME_ENVIROMENT','DEV');
//开发与部署的切换
if(in_array(RUNTIME_ENVIROMENT, ['TEST','DEV','LOCAL'])){
    $debug = true;
} else {
    $debug = false;
}
// 开启调试模式
define('APP_DEBUG',$debug);
//关闭目录安全文件的生成
define('BUILD_DIR_SECURE',false);
//CLI
define('APP_MODE','cli');

// 定义应用目录
define('APP_PATH',dirname(__FILE__).'/Application/');

require './vendor/autoload.php';

//引入命令行工具文件
include_once dirname(__FILE__).'/ThinkPHP/Common/CommandLine.php';
// 引入ThinkPHP入口文件
require dirname(__FILE__).'/ThinkPHP/ThinkPHP.php';


