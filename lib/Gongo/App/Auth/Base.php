<?php
class Gongo_App_Auth_Base extends Gongo_App_Base
{
	protected $loginUser = false;
	protected $isValid = false;
	
	public $uses = array(
		'-columnPassword' => 'password',
		'-sessionName' => 'loginUser',
		'-regenerateSessionId' => true,
	);
	
	public function loginUser($value = null)
	{
		if (is_null($value)) return $this->loginUser;
		$this->loginUser = $value;
		return $this;
	}
	
	public function isValid($app = null)
	{
		return $this->isValid;
	}
	
	public function authenticate($app, $callback)
	{
	}

	public function readLoginUser($app)
	{
		return $app->session->{$this->options->sessionName};
	}

	public function writeLoginUser($app, $value)
	{
		$app->session->{$this->options->sessionName} = $value;
		return $value;
	}

	public function deleteLoginUser($app)
	{
		$app->session->{$this->options->sessionName} = null;
	}

	public function login($app, $user)
	{
		if ($this->options->regenerateSessionId) session_regenerate_id(true);
		if ($user) {
			$user = $this->processLogin($app, $user);
			$this->loginUser($user);
			$this->writeLoginUser($app, $user);
		}
	}

	public function logout($app)
	{
		$user = $this->readLoginUser($app);
		$this->processLogout($app, $user);
		$this->loginUser(false);
		$this->deleteLoginUser($app);
	}

	public function processLogin($app, $user) 
	{
		return $user;
	}

	public function processLogout($app, $user) 
	{
	}

}
