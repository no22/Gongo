<?php
class Gongo_App_Auth_Basic extends Gongo_App_Auth_Base
{
	public $uses = array(
		'-message' => 'Please Enter Your Password',
		'-regenerateSessionId' => false,
	);
	
	public function outputHeader()
	{
		header('WWW-Authenticate: Basic realm="' . $this->options->message . '"');
	}

	public function getHttpAuthInfo($app)
	{
		return array($app->server->PHP_AUTH_USER, $app->server->PHP_AUTH_PW);
	}
	
	public function authenticate($app, $callback)
	{
		list ($authUser, $authPasswd) = $this->getHttpAuthInfo($app);
		if (!$authUser) {
			$app->error('401', $this->_outputHeader());
		} else {
			$user = call_user_func($callback, $authUser);
			if (!$user) $app->error('401', $this->_outputHeader());
			$password = $user->{$this->options->columnPassword};
			if ($password && ($password === $authPasswd)) {
				$this->isValid = true;
				$this->login($app, $user);
			} else {
				$app->error('401', $this->_outputHeader());
			}
		}
	}
}
