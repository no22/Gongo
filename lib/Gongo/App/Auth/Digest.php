<?php
class Gongo_App_Auth_Digest extends Gongo_App_Auth_Base
{
	public $uses = array(
		'-message' => 'Please Enter Your Password',
		'-regenerateSessionId' => false,
	);
	
	public function outputHeader()
	{
		header('WWW-Authenticate: Digest realm="' . $this->options->message . 
			'", nonce="' . uniqid() . '", qop="auth", opaque="' . 
			md5($this->options->message) . '"'
		);
	}
	
	public function httpDigestParse($text)
	{
		$needed = array(
			'nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 
			'username' => 1, 'uri' => 1, 'response' => 1
		);
		$data = array();
		$keys = implode('|', array_keys($needed));
		preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $text, $matches, PREG_SET_ORDER);
		foreach ($matches as $m) {
			$data[$m[1]] = $m[3] ? $m[3] : $m[4] ;
			unset($needed[$m[1]]);
		}
		return $needed ? false : $data ;
	}
	
	public function authenticate($app, $callback)
	{
		if (!$app->server->PHP_AUTH_DIGEST) {
			$headers = getallheaders();
			if (isset($headers['Authorization'])) {
				$app->server->PHP_AUTH_DIGEST = $headers['Authorization'];
			}
		}
		if ($app->server->PHP_AUTH_DIGEST) {
			$data = $this->httpDigestParse($app->server->PHP_AUTH_DIGEST);
			$user = isset($data['username']) ? $data['username'] : false ;
			$bean = call_user_func($callback, $data['username']);
			if (!$bean) $app->error('401', $this->_outputHeader());
			$passwd = $bean->{$this->options->columnPassword};
			if ($data && $user && $passwd) {
				$A1 = md5($data['username']. ':' . $this->options->message . ':' . $passwd);
				$A2 = md5($app->server->REQUEST_METHOD . ':' . $data['uri']);
				$validResponse = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
				if ($data['response'] != $validResponse) {
					unset($app->server->PHP_AUTH_DIGEST);
					$app->error('401', $this->_outputHeader());
				} else {
					$this->isValid = true;
					$this->login($app, $bean);
				}
			}
		} else {
			$app->error('401', $this->_outputHeader());
		}
	}
}
