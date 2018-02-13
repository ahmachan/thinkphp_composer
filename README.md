使用 Composer 为 ThinkPHP（3.2.3）框架添加和管理组件

创建Composer时：sudo curl -s https://getcomposer.org/installer | php
composer会寻找php.ini的地址,如果composer create-project出现 /usr/bin/env: php: permission denied
则可能是因为找不对应php安装目录，可以尝试重新安装composer，或链接正确的php目录，默认比如是 /usr/local/bin/php

测试使用 ThinkPHP（3.2.3） 框架（也可以使用 Laravel、Yii 等其他现代框架）。
使用命令行初始化的框架，将产生一个composer.json文件.

#01 命令行方式创建项目文件
composer create-project [name] [projectname]

composer create-project topthink/thinkphp project-composer2 将下载对应文件并生产composer.json文件：
{
    "name": "topthink/thinkphp",
    "description": "the ThinkPHP Framework",
    "type": "framework",
    "keywords": ["framework","thinkphp","ORM"],
    "homepage": "http://thinkphp.cn/",
    "license": "Apache2",
    "authors": [
        {
            "name": "liu21st",
            "email": "liu21st@gmail.com"
        }
    ],
    "require": {
        "php": ">=5.3.0"
    },
    "minimum-stability": "dev",
    "repositories": [
        {"type": "composer", "url": "http://packagist.phpcomposer.com"},
        {"packagist": false}
    ]
}

#02.下载对应项目文件
composer update
如果此时产生异常：
Loading composer repositories with package information
[Composer\Downloader\TransportException]
Your configuration does not allow connections to http://packagist.phpcomposer.com/packages.json. See https://getcomposer.org/doc/06-config.md#secure-http for details.

需要将 composer.json 中的以下 url 由http改为https，最终结果是;
"repositories": [
{"type": "composer", "url": "https://packagist.phpcomposer.com"},
{"packagist": false}
]

#03.添加相关依赖文件(可命令行方式与手动方式)
composer require file/whoops
./composer.json has been updated:

{
    "name": "topthink/thinkphp",
    "description": "the ThinkPHP Framework",
    "type": "framework",
    "keywords": ["framework","thinkphp","ORM"],
    "homepage": "http://thinkphp.cn/",
    "license": "Apache2",
    "authors": [
        {
            "name": "liu21st",
            "email": "liu21st@gmail.com"
        }
    ],
    "require": {
        "php": ">=5.3.0",
        "filp/whoops": "^2.1"
    },
    "minimum-stability": "dev",
    "repositories": [
        {"type": "composer", "url": "https://packagist.phpcomposer.com"},
        {"packagist": false}
    ]
}

如果安装指定版本的：composer require monolog/monolog:1.21.0
./composer.json has been updated
Loading composer repositories with package information
Updating dependencies (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
  - Installing monolog/monolog (1.21.0): Downloading (100%)         
monolog/monolog suggests installing aws/aws-sdk-php (Allow sending log messages to AWS services like DynamoDB)
monolog/monolog suggests installing doctrine/couchdb (Allow sending log messages to a CouchDB server)
monolog/monolog suggests installing ext-amqp (Allow sending log messages to an AMQP server (1.0+ required))
monolog/monolog suggests installing ext-mongo (Allow sending log messages to a MongoDB server)
monolog/monolog suggests installing graylog2/gelf-php (Allow sending log messages to a GrayLog2 server)
monolog/monolog suggests installing mongodb/mongodb (Allow sending log messages to a MongoDB server via PHP Driver)
monolog/monolog suggests installing php-amqplib/php-amqplib (Allow sending log messages to an AMQP server using php-amqplib)
monolog/monolog suggests installing php-console/php-console (Allow sending log messages to Google Chrome)
monolog/monolog suggests installing rollbar/rollbar (Allow sending log messages to Rollbar)
monolog/monolog suggests installing ruflin/elastica (Allow sending log messages to an Elastic Search server)
monolog/monolog suggests installing sentry/sentry (Allow sending log messages to a Sentry server)
Writing lock file
Generating autoload files


#04.引入autoload.php
在入口文件这样引入：
require './vendor/autoload.php';
require './ThinkPHP/ThinkPHP.php';
注意autoload要先引入

