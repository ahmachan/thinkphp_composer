<?php
//数据库配置
$databaseConfig = include APP_PATH . 'Home/Conf/config.database.php';
$config=[];
$config = array_merge($config, $databaseConfig);

$oauth2tableConfig = include APP_PATH . 'Home/Conf/config.oauth2tables.php';

$config = array_merge($config, $oauth2tableConfig);

unset($databaseConfig,$oauth2tableConfig);

return $config;
