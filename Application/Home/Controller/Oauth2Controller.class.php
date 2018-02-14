<?php
namespace Home\Controller;

use Think\Controller;

use Org\Auth\ThinkOAuth2;

class Oauth2Controller extends BaseController
{

	private $oauth = NULL;
	private $_user_id;

	function _initialize(){
		$this->oauth = new ThinkOAuth2();
    }
    
    //获取应用网站数据:/Home/Oauth2/getRedirectUri?client_id=101138195&client_key=aaa&scope=read
    public function getRedirectUri(){
    	//$client_id = $_GET['client_id'];
    	$client_id= I('client_id','');
    	//$user_id   = $_SESSION['my_info']['uid'];
    	$user_id =1024;
    	if($this->oauth->checkClientCredentials($client_id)){//判断应用是否为授权应用
    		$client = $this->oauth->getRedirectUri($client_id);
    		$code = md5($client_id.$user_id);//构建验证码  这里可以采用自己的一些加密手段
    		$redirect_uri = $client.'?code='.$code;//定义回调函数
    		if(!$this->oauth->getAuthCode($code)){//判断验证码的存在
    			$this->oauth->setAuthCode($code,$user_id,$client_id,$redirect_uri,3600);//不存在就创建
    		}
    	}
    	//echo '<a href="'.$redirect_uri.'">授权</a>';
    	$postUrl="http://composer-tp32.local.cc/index.php/Home/Oauth2/getAccessToken";
    	echo $this->renderTpl($code,$client_id,$postUrl);
    }
    
    private function renderTpl($code,$clientId,$postUrl){
        $strHTML= <<<TPL
<form name="post_form" method="POST" action="{$postUrl}">
<input name="client_id" type="hidden" value="{$clientId}" />
<input name="code" type="hidden" value="{$code}" />
<p><button name="submitBtn" type="submit">授权</button></p>
</form>
TPL;
          return $strHTML;
    }
    
    //获取到应用网站token:/Home/Oauth2/getAccessToken code=,client_id
    public function getAccessToken(){
        $code=trim($_POST['code']);
        $clientId=$_POST['client_id'];
        $userIdRes = $this->oauth->checkUser($code);
        $userId=$userIdRes['user_id'];
       
        $accessToken = md5($clientId.$userId.$code.\microtime(true));
        $refreshToken= md5($useId.$code.\microtime(true));
        $tokenList =$this->oauth->getAccessTokenByClientIdUserId($clientId,$userId);
        $tokenRes = $tokenList[0];
        $stampTime = \time();
        if(!$tokenRes){//不存在登陆过的用户要创建授权码
            $expires=$stampTime+(60*2);//2min
    	    $this->oauth->setAccessToken($accessToken,$userId,$clientId,$refreshToken,$expires);//为新用户创建授权码
    	}else{
    	    $expires= $tokenRes['expires_in'];    	   
    	    //如果过期则重设置ACCESSTOKEN
    	    if($stampTime>=$expires){
    	        $expires=$stampTime+(60*2);//2min
    	        $this->oauth->updateAccessTokenByClientIdUserId($accessToken,$userId,$clientId,$refreshToken,$expires);
    	    }
    	}
    	
    	$data = $this->oauth->getAccessTokenByClientIdUserId($clientId,$userId);//获取用户授权码
    	echo json_encode($data[0]);
    }
    
    public function getLoggedInUser(){
    	$access_token = $_GET['access_token'];
    	$data = $this->oauth->getAccessToken($access_token);
    	if($access_token == md5($data[0]['user_id'].$data[0]['refresh_token'])){
    		$user = M('member')->field('uid,username')->find($data[0]['user_id']);
    		$user['uname'] = $user['username'];
    	}
    	echo json_encode($user);
    }
}