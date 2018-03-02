<?php

namespace Org\Auth;

/**
 * @category ORG
 * @package ORG
 * @author Leyteris
 * @version 2012.3.16
 */

// OAUTH2_DB_DSN  数据库连接DSN
// OAUTH2_CODES_TABLE 服务器表名称
// OAUTH2_CLIENTS_TABLE 客户端表名称
// OAUTH2_TOKEN_TABLE 验证码表名称

//import("ORG.OAuth.OAuth2");

use Think\Db;

class ThinkOAuth2 extends OAuth2 {
    /**
     * db object
     * @var Think\Db
     */
	private $db;
	/**
	 * db tables
	 * @var array
	 */
	private $table;

	/**
	 * 构造
	 */
	public function __construct() {
		parent::__construct();
		$dbConfig=[
		    'db_type'   => C('db_type'),
		    'db_user'   => C('db_user'),
		    'db_pwd'    => C('db_pwd'),
		    'db_host'   => C('db_host'),
		    'db_port'   => C('db_port'),
		    'db_name'   => C('db_name'),
		    'DB_PREFIX' => C('DB_PREFIX')
		];
		$this -> db = Db::getInstance($dbConfig);
		$this -> table = array(
			'auth_code'=>C('OAUTH2_CODES_TABLE'),
			'auth_client'=>C('OAUTH2_CLIENTS_TABLE'),
			'auth_token'=>C('OAUTH2_TOKENS_TABLE')
		);
	}

	/**
	 * 析构
	 */
	function __destruct() {
		$this->db = NULL; // Release db connection
	}

	private function handleException($e) {
		echo "Database error: " . $e->getMessage();
		exit;
	}

	/**
	 *
	 * 增加client
	 * @param string $client_id
	 * @param string $client_secret
	 * @param string $redirect_uri
	 */
	public function addClient($client_id, $client_secret, $redirect_uri) {
		$time = time();
		$sql = "INSERT INTO {$this -> table['auth_client']} (client_id, client_secret, redirect_uri, create_time) VALUES ('{$client_id}', '{$client_secret}', '{$redirect_uri}','{$time}')";
		$this -> db -> execute($sql);
	}

	/**
	 * Implements OAuth2::checkClientCredentials()
	 * @see OAuth2::checkClientCredentials()
	 */
	public function checkClientCredentials($client_id, $client_secret = NULL) {

		$sql = "SELECT client_secret FROM {$this -> table['auth_client']} WHERE client_id = '{$client_id}'";
		$result = $this -> db -> query($sql);
		if ($client_secret === NULL) {
			return $result !== FALSE;
		}
		//Log::write("checkClientCredentials : ".$result);
		//Log::write("checkClientCredentials : ".$result[0]);
		//Log::write("checkClientCredentials : ".$result[0]["client_secret"]);

		return $result[0]["client_secret"] == $client_secret;

	}

	/**
	 * Implements OAuth2::getRedirectUri().
	 * @see OAuth2::getRedirectUri()
	 */
	public function getRedirectUri($client_id) {
		$sql = "SELECT redirect_uri FROM {$this -> table['auth_client']} WHERE client_id = '{$client_id}'";
		$result = $this -> db -> query($sql);
		if ($result === FALSE) {
			return FALSE;
		}
		//Log::write("getRedirectUri : ".$result);
		//Log::write("getRedirectUri : ".$result[0]);
		//Log::write("getRedirectUri : ".$result[0]["redirect_uri"]);

		return isset($result[0]["redirect_uri"]) && $result[0]["redirect_uri"] ? $result[0]["redirect_uri"] : NULL;

	}

	/**
	 * Implements OAuth2::getAccessToken().
	 * @see OAuth2::getAccessToken()
	 */
	public function getAccessToken($access_token) {
		$sql = "SELECT client_id, user_id, access_token, refresh_token, expires_in, scope FROM {$this -> table['auth_token']} WHERE access_token = '{$access_token}'";
		$result = $this -> db -> query($sql);
		//Log::write("getAccessToken : ".$result);
		//Log::write("getAccessToken : ".$result[0]);

		return $result !== FALSE ? $result : NULL;

	}

	/**
	 * Implements OAuth2::setAccessToken().
	 * @see OAuth2::setAccessToken()
	 */
	public function setAccessToken($access_token, $user_id, $client_id, $refresh_token, $expires, $scope = NULL) {
		$sql = "INSERT INTO {$this -> table['auth_token']} (access_token, user_id, client_id, refresh_token, expires_in, scope) VALUES ('{$access_token}', '{$user_id}', '{$client_id}', '{$refresh_token}', '{$expires}', '{$scope}')";
		$this -> db -> execute($sql);
	}

	/**
	 * Overrides OAuth2::getSupportedGrantTypes().
	 * @see OAuth2::getSupportedGrantTypes()
	 */
	public function getSupportedGrantTypes() {
		return array(
			OAUTH2_GRANT_TYPE_AUTH_CODE
		);
	}

	/**
	 * Overrides OAuth2::getAuthCode().
	 * @see OAuth2::getAuthCode()
	 */
	public function getAuthCode($code) {
		$sql = "SELECT `code`, `client_id`, `redirect_uri`, `expires`, `scope` FROM {$this -> table['auth_code']} WHERE `code` = '{$code}'";
		$result = $this -> db -> query($sql);
		return $result !== FALSE ? $result[0] : NULL;
	}
	
	public function getClientByClientId($clientId){
	    $sql = <<<SQL
        Select t.`client_id`,t.`redirect_uri`
        From {$this -> table['auth_client']} as t
        WHERE t.`client_id` = '{$clientId}' limit 1;
SQL;
	    $result = $this -> db -> query($sql);
	    if ($result === FALSE) {
	        return FALSE;
	    }
	    return $result;
	}
	
	public function getAuthclientBySecretkey($clientId,$secretKey){
	    $sql = <<<SQL
        Select t.`client_id`,t.`redirect_uri`,c.`user_id`,c.`code`,c.`expires`,c.`scope` 
        From {$this -> table['auth_code']} as c Left join {$this -> table['auth_client']} as t on t.client_id=c.client_id
        WHERE t.`client_id` = '{$clientId}' and t.`client_secret`='{$secretKey}';
SQL;

	    $result = $this -> db -> query($sql);
	    if ($result === FALSE) {
	        return FALSE;
	    }
	    return $result;
	}
	
	public function initClient($clientId,$secretKey, $redirectUri,$code, $userId, $expires, $scope = NULL){
	    $doneRes = $this->addClient($clientId, $secretKey, $redirectUri);
	    //事务？？
	    if ($doneRes){
	        $result = $this->setAuthCode($code, $userId, $clientId, $redirectUri, $expires,$scope);
	        return $result;
	    }
	    return false;
	}

	/**
	 * Overrides OAuth2::setAuthCode().
	 * @see OAuth2::setAuthCode()
	 */
	public function setAuthCode($code, $user_id, $client_id, $redirect_uri, $expires, $scope = NULL) {
		//$time = time();
		$sql = "INSERT INTO {$this -> table['auth_code']} (`code`, `user_id`, `client_id`, `redirect_uri`, `expires`, `scope`) VALUES ('{$code}', '{$user_id}', '{$client_id}', '{$redirect_uri}', '{$expires}', '{$scope}')";
		$result = $this -> db -> execute($sql);
		return $result;
    }
  
    public function checkUser($code){
	  	$sql = "SELECT user_id FROM {$this -> table['auth_code']} WHERE `code` = '{$code}'";
	  	$result = $this -> db -> query($sql);
	  	return $result !== FALSE ? $result[0] : NULL;
    }
    
    
    public function updateAccessTokenByClientIdUserId($accessToken,$userId,$clientId,$refreshToken,$expires){
        $sql = <<<SQL
        UPDATE {$this -> table['auth_token']}
        SET
        `access_token` = '{$accessToken}',
        `refresh_token` = '{$refreshToken}',
        `expires_in` = {$expires}
        WHERE `client_id` ='{$clientId}' and `user_id`= {$userId};
SQL;
        //没有任何数据变化时，返回0行影响行数
        $doneRes = $this -> db -> execute($sql);
        if (!$doneRes) {
            if ("" == $this->getDbError()) {
                $doneRes = true;
            }
        }
        return $doneRes;
    }
    
    public function getAccessTokenByClientIdUserId($clientId,$userId){
        $sql = "SELECT `user_id`,`client_id`,`access_token`,`refresh_token`,`expires_in` FROM {$this -> table['auth_token']} WHERE `client_id` ='{$clientId}' and `user_id`= {$userId}";
        $result = $this -> db -> query($sql);
        return $result !== FALSE ? $result[0] : NULL;
    }

  /**
   * Overrides OAuth2::checkUserCredentials().
   * @see OAuth2::checkUserCredentials()
   */
  public function checkUserCredentials($client_id, $username, $password){
  	return TRUE;
  }
}