<?php
/**
 * CommandLine args
 *
 */
class CommandLine
{
    public static $args;

    /**
     * PARSE ARGUMENTS，增加命令行传参数：参数名必须以"--"开头字符串，参数值不能以"-"开头的字符串
     *
     * # 基本方法.
     * $ php test.php [/apply/controller/function] --foo --bar=baz --spam dog
     *   ["foo"]   => true
     *   ["bar"]   => "baz"
     *   ["spam"]  => "dog"
     * 
     * # 简单数组.
     * $ php test.php --types=[1,2,3,4]  
     *   ["types"] => [1,2,3,4]
     *   
     * # json数组(注意需要单引号包含)，不建议使用,可化作: --coupon_page 2  
     * $ php test.php [/apply/controller/function] --coupon '{"type":"face","limit":"20","page":2}'  
     *   ["coupon"] => ['type'=>'face','limit'=>20,'page'=>2]
     * @usage $args = CommandLine::parseArgs();
     */
    public static function parseArgs($argvs = null)
    {
        $argv = (\is_null($argvs) || empty($argvs)) ? $_SERVER['argv'] : $argvs;
        array_shift($argv);
        
        $out = array();

        for ($i = 0, $j = count($argv); $i < $j; $i ++) {
            $arg = $argv[$i];
            
            // --foo --bar=baz
            if (substr($arg, 0, 2) === '--') {
                $eqPos = strpos($arg, '=');
                
                // --foo
                if ($eqPos === false) {
                    $key = substr($arg, 2);
                    
                    // --foo value
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i ++;
                    } else {
                        $value = isset($out[$key]) ? $out[$key] : true;
                    }
                    $out[$key] = $value;
                }                
                // --bar=baz
                else {
                    $key = substr($arg, 2, $eqPos - 2);
                    $value = substr($arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            }
        }
        
        foreach ($out as $key => $val) {
            $isSimpleArr = preg_match('/^\[(.*?)\]$/', $val);//[xxxx]
            $isJsonArr = preg_match('/^\{(.*?)\}$/', $val);//'{xxxx}'
            if ($isSimpleArr || $isJsonArr) {
                if ($isSimpleArr) {
                    $out[$key] = \json_decode(strval($val), true);
                }
                if ($isJsonArr) {
                    $out[$key] = \json_decode(strval($val), true);
                }
            } else {
                continue;
            }
        }

        self::$args = $out;
        
        return $out;
    }
    
    /**
     * GET BOOLEAN
     */
    public static function getBoolean($key, $default = false)
    {
        if (! isset(self::$args[$key])) {
            return $default;
        }
        $value = self::$args[$key];
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_int($value)) {
            return (bool) $value;
        }
        
        if (is_string($value)) {
            $value = strtolower($value);
            $map = array(
                'y' => true,
                'n' => false,
                'yes' => true,
                'no' => false,
                'true' => true,
                'false' => false,
                '1' => true,
                '0' => false,
                'on' => true,
                'off' => false
            );
            if (isset($map[$value])) {
                return $map[$value];
            }
        }
        
        return $default;
    }
}
