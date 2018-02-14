<?php
//'配置项'=>'配置值'
if (getenv('RUNTIME_ENVIRONMENT') == "DEV") {
    $databaseConfig = [
        //后台数据库
        'db_type'   => 'mysql',
        'db_user'   => 'root',
        'db_pwd'    => 'abc123yy',
        'db_host'   => 'mysql5.6.16',
        'db_port'   => '3306',
        'db_name'   => 'oauth2org',
        'DB_PREFIX' => 'oauth_'
    ];
} elseif (getenv('RUNTIME_ENVIRONMENT') == "TEST") {
    $databaseConfig = [
        //后台数据库
        'db_type'   => 'mysql',
        'db_user'   => 'root',
        'db_pwd'    => 'abc123yy',
        'db_host'   => 'mysql5.6.16',
        'db_port'   => '3306',
        'db_name'   => 'oauth2org',
        'DB_PREFIX' => 'oauth_'
    ];
} else {
    $databaseConfig = [
        //后台数据库
        'db_type'   => 'mysql',
        'db_user'   => 'root',
        'db_pwd'    => 'abc123yy',
        'db_host'   => 'mysql5.6.16',
        'db_port'   => '3306',
        'db_name'   => 'oauth2org',
        'DB_PREFIX' => 'oauth_'
    ];
}

return $databaseConfig;

