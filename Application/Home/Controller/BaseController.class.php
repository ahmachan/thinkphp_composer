<?php
namespace Home\Controller;

use Think\Controller;

use Whoops\Exception\Frame;
use Whoops\Exception\Inspector;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class BaseController extends Controller
{
     /**
     * 缓存时间戳
     * @var integer
     */
    private static $timestamp = 0;
    
    /**
     * 程序执行id
     * @var string
     */
    private static $tractId = '';
    
    /**
     * 运行环境变量
     * @var string
     */
    private static $runtimeEnv = '';
    

     
    /**
     * 获取程序执行时间
     * @return number
     */
    public static function getTimestamp()
    {
        return self::$timestamp;
    }
    
    /**
     * 初始化环境变量
     * @return boolean
     */
    private function initEnv()
    {
        self::$tractId = TRANCE_ID;
        self::$runtimeEnv = \strtolower(RUNTIME_ENVIRONMENT);
        self::$timestamp = time();
        return true;
    }
    
    /**
     * 获得nginx传过来的服务器环境变量
     * @return string
     */
    public static function getRuntimeEnv()
    {
        return self::$runtimeEnv;
    }
    
    /**
     * 读取请求唯一 id
     * @return string
     */
    public static function getTractId()
    {
        return self::$tractId;
    }
    

    /**
     * 用户信息
     */
    public $adminInfo = [];

    public $adminId = 0;


    public function __construct()
    {
        parent::__construct();
        $this->initEnv();
        $this->exceptionRegister();
    }

    
    /**
     * 注册whoops
     * @return boolean
     */
    public function exceptionRegister()
    {
        $env = $this->getRuntimeEnv();
        // 设置Whoops提供的错误和异常处理
        $whoops = new \Whoops\Run();
        if (in_array($env,['dev','test'])) {
            $handler = new PrettyPageHandler();
            $handler->addDataTableCallback('SQL Stack', function(){
                return 'Database SqlStack';
            });
            $whoops->pushHandler($handler);
        } else {
            $whoops->pushHandler(function($exception, Inspector $inspector, $run){
                $frames = $inspector->getFrames();
                $traceArr = [
                    'sqlStack' => [],
                    'stackTrace' => [],
                ];
                foreach ($frames as $frame) {
                    $file = $frame->getFile();
                    $class = $frame->getClass();
                    $function = $frame->getFunction();
                    $line = $frame->getLine();
                    $stackTraceStr = 'FILE: '.$file.' CLASS: '.$class .'::'. $function.', LINE: '.$line;
                    \array_push($traceArr['stackTrace'], $stackTraceStr);
                }
                
                echo "<h1>Whoops, something bad is happened</h1>";
                //print_r($traceArr);
                return Handler::DONE;
            });
        }
        $whoops->register();
        return true;
    }
    
}
