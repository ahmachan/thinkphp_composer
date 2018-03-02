<?php
namespace Home\Controller;

use Think\Controller;


class TestController extends BaseController
{
  /**
   * @example /Application/Test/index
   */
  public function index() {
        //$cliArgs = \CommandLine::parseArgs();
        //echo "\n************\$cliArgs*********\n";
        //print_r($cliArgs);  

        // 测试未捕获的异常
        $this->division(10, 0);
    }

    private function division($dividend, $divisor) {
        if($divisor == 0) {
            throw new \Exception('Division by zero');
        }
        
        return $dividend/$divisor;
    }
    
    // Home/Test/handleAuthCall
    public function handleAuthCall(){
        
    }
}
