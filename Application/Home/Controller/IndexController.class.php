<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends BaseController
{
    /**
     * /Home/Index/index
     */
    public function index()
    {
        $cliArgs = \CommandLine::parseArgs();
        echo "\n************\$cliArgs*********\n";
        print_r($cliArgs);  
    }
    
    public function home(){
        print_r($_GET);
        exit();
        echo 'thinkphp 3.2.3 composer home...';
    }
}