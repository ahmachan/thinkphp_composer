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
    
    //
    public function initClient(){
        $userId= I('get.user_id',0);
        $clientId = \microtime(true)+time();
        $secretKey= md5(\random_int(10000000,99999999));
        $redirectUri='http://composer-tp32.local.cc/index.php/Home/Oauth2';
        $code = md5($clientId.$userId.\microtime(true));//构建验证码  这里可以采用自己的一些加密手段
        if($userId<=0){
            echo "user id error";
            return false;
        }
        $res = $this->oauth->initClient($clientId, $secretKey, $redirectUri, $code, $userId, 3600);//创建
        return $res;
    }
    
    /**
     * Home/Oauth2/authorize?client_id=101138195&response_type=code&scope=read&redirect_uri=xxx
     */
    public function authorize(){
        /**
         * 用户同意授权，获取code    
    在确保微信公众账号拥有授权作用域（scope参数）的权限的前提下（服务号获得高级接口后，默认拥有scope参数中的snsapi_base和snsapi_userinfo），引导关注者打开如下页面：
    https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
         */
        $clientId= I('get.client_id','');
        $redirectUri= I('get.redirect_uri','');
        $responseType = 'code';
        $scope = 'read';
        $resData = $this->oauth->getClientByClientId($clientId);
        if(!empty($resData)){
            $resData = array_merge($resData[0],['response_type'=>"{$responseType}",'scope'=>"{$scope}"]);
        }
        $postUrl="";
        echo $this->renderCodeTpl($clientId, $postUrl);
    }
    
    private function renderCodeTpl($clientId,$postUrl){
        $strHTML= <<<TPL
<form name="post_form" method="POST" action="{$postUrl}">
<input name="client_id" type="hidden" value="{$clientId}" />
<p><button name="submitBtn" type="submit">授权</button></p>
</form>
TPL;
        return $strHTML;
    }
    
    /*
参数说明
参数	是否必须	说明
appid	是	公众号的唯一标识
redirect_uri	是	授权后重定向的回调链接地址， 请使用 urlEncode 对链接进行处理
response_type	是	返回类型，请填写code
scope	是	应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且， 即使在未关注的情况下，只要用户授权，也能获取其信息 ）
state	否	重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
#wechat_redirect	是	无论直接打开还是做页面302重定向时候，必须带此参数

用户同意授权后
如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。
code说明 ： code作为换取access_token的票据，每次用户授权带上的code将不一样，code只能使用一次，5分钟未被使用自动过期。

获取code后，请求以下链接获取access_token：  https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
    */
    ///Home/Oauth2/getAuthCode?client_id=101138195&secret_key=bf701490797ea7390333be017fbe786a&scope=read
    public function getAuthCode(){
        $clientId= I('get.client_id','');
        $secretKey= I('get.secret_key','');
        $res = $this->oauth->getAuthclientBySecretkey($clientId, $secretKey);
        if (!$this->oauth->checkClientCredentials($clientId)) { // 判断是否存在授权应用
            echo "not found client";
            return false;
        }
        
        $code = md5($client_id.\microtime(true));//构建验证码  这里可以采用自己的一些加密手段
        $redirect_uri = $client.'?code='.$code;//定义回调函数
        if(!$this->oauth->getAuthCode($code)){//判断验证码的存在
            $this->oauth->setAuthCode($code,$user_id,$client_id,$redirect_uri,3600);//不存在就创建
        }
        
        echo json_encode($res);
    }
    
    //获取应用网站数据:/Home/Oauth2/getRedirectUri?client_id=101138195&client_key=aaa&scope=read
    public function getRedirectUri(){
    	$client_id= I('get.client_id','');
    	if($this->oauth->checkClientCredentials($client_id)){//判断应用是否为授权应用
    		$client = $this->oauth->getRedirectUri($client_id);
    		$code = md5($client_id.\microtime(true));//构建验证码  这里可以采用自己的一些加密手段
    		$redirect_uri = $client.'?code='.$code;//定义回调函数
    		if(!$this->oauth->getAuthCode($code)){//判断验证码的存在
    			$this->oauth->setAuthCode($code,$user_id,$client_id,$redirect_uri,3600);//不存在就创建
    		}
    	}
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
    
    
    //获取code后，请求以下链接获取access_token：  
    //https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
    /**
     * 正确时返回的JSON数据包如下：

{ "access_token":"ACCESS_TOKEN",
"expires_in":7200,
"refresh_token":"REFRESH_TOKEN",
"openid":"OPENID",
"scope":"SCOPE" }
参数	描述
access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
expires_in	access_token接口调用凭证超时时间，单位（秒）
refresh_token	用户刷新access_token
openid	用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
scope	用户授权的作用域，使用逗号（,）分隔
错误时微信会返回JSON数据包如下（示例为Code无效错误）:

{"errcode":40029,"errmsg":"invalid code"}

     */
    //获取到应用网站token:/Home/Oauth2/getAccessToken?client_id=APPID&secret_key=SECRET&code=CODE&grant_type=authorization_code
    public function getAccessToken(){
        $code=I('code','');
        $clientId=I('client_id','');
        $userIdRes = $this->oauth->checkUser($code);
        $userId=$userIdRes['user_id'];
       
        $accessToken = md5($clientId.$userId.$code.\microtime(true));
        $refreshToken= md5($useId.$code.\microtime(true));
        $tokenRes=$this->oauth->getAccessTokenByClientIdUserId($clientId,$userId);
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
    	echo json_encode($data);
    }
    
    /**
     * 刷新access_token（如果需要）

由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token有效期为30天，当refresh_token失效之后，需要用户重新授权。

请求方法

获取第二步的refresh_token后，请求以下链接获取access_token：
https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=APPID&grant_type=refresh_token&refresh_token=REFRESH_TOKEN
参数	是否必须	说明
appid	是	公众号的唯一标识
grant_type	是	填写为refresh_token
refresh_token	是	填写通过access_token获取到的refresh_token参数
返回说明

正确时返回的JSON数据包如下：

{ "access_token":"ACCESS_TOKEN",
"expires_in":7200,
"refresh_token":"REFRESH_TOKEN",
"openid":"OPENID",
"scope":"SCOPE" }
参数	描述
access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
expires_in	access_token接口调用凭证超时时间，单位（秒）
refresh_token	用户刷新access_token
openid	用户唯一标识
scope	用户授权的作用域，使用逗号（,）分隔
错误时微信会返回JSON数据包如下（示例为code无效错误）:

{"errcode":40029,"errmsg":"invalid code"}

第四步：拉取用户信息(需scope为 snsapi_userinfo)

如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过access_token和openid拉取用户信息了。

请求方法

http：GET（请使用https协议） https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
参数说明

参数	描述
access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
openid	用户的唯一标识
lang	返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
返回说明

正确时返回的JSON数据包如下：

{    "openid":" OPENID",
" nickname": NICKNAME,
"sex":"1",
"province":"PROVINCE"
"city":"CITY",
"country":"COUNTRY",
"headimgurl":    "http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",
"privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],
"unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
}
参数	描述
openid	用户的唯一标识
nickname	用户昵称
sex	用户的性别，值为1时是男性，值为2时是女性，值为0时是未知
province	用户个人资料填写的省份
city	普通用户个人资料填写的城市
country	国家，如中国为CN
headimgurl	用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。
privilege	用户特权信息，json 数组，如微信沃卡用户为（chinaunicom）
unionid	只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。
错误时微信会返回JSON数据包如下（示例为openid无效）:

{"errcode":40003,"errmsg":" invalid openid "}

附：检验授权凭证（access_token）是否有效

请求方法

http：GET（请使用https协议） https://api.weixin.qq.com/sns/auth?access_token=ACCESS_TOKEN&openid=OPENID
参数说明

参数	描述
access_token	网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
openid	用户的唯一标识
返回说明
正确的JSON返回结果：

{ "errcode":0,"errmsg":"ok"}
错误时的JSON返回示例：

{ "errcode":40003,"errmsg":"invalid openid"}
     */
    public function getLoggedInUser(){
    	$access_token = $_GET['access_token'];
    	$data = $this->oauth->getAccessToken($access_token);
    	if($access_token == md5($data[0]['user_id'].$data[0]['refresh_token'])){
    		$user = M('member')->field('uid,username')->find($data[0]['user_id']);
    		$user['uname'] = $user['username'];
    	}
    	echo json_encode($user);
    }
    
    /**
     * 接口调用请求说明
https请求方式: (公众号可以使用AppID和AppSecret调用本接口来获取access_token)
https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
参数说明

参数	是否必须	说明
grant_type	是	获取access_token填写client_credential
appid	是	第三方用户唯一凭证
secret	是	第三方用户唯一凭证密钥，即appsecret
返回说明

正常情况下，微信会返回下述JSON数据包给公众号：

{"access_token":"ACCESS_TOKEN","expires_in":7200}
参数说明

参数	说明
access_token	获取到的凭证
expires_in	凭证有效时间，单位：秒
错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:

{"errcode":40013,"errmsg":"invalid appid"}
返回码说明
     */
}