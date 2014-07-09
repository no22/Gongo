<?php
class Gongo_App_Auth extends Gongo_App_Auth_Base
{
	public $uses = array(
		'crypt' => null,
		'userProxy' => null,
		'-login' => '/login',
		'-redirect' => '/',
		'-force' => false,
		'-postfix' => 'Redirect',
		'-loginFormUserId' => 'id',
		'-loginFormPassword' => 'password',
		'-errorName' => 'loginError',
		'-columnPasswordSalt' => 'salt',
	);

	public function isLoginUrl($app, $url = null)
	{
		$url = is_null($url) ? $app->url->requestUrl : $url ;
		return strpos($url, $app->replacePathArgs($this->options->login), 0) === 0;
	}

	public function isValid($app = null)
	{
		$loginUser = $this->readLoginUser($app);
		$this->isValid = !empty($loginUser);
		return $this->isValid;
	}

	public function isValidPassword($password, $crypted, $salt = '')
	{
		return (!$this->crypt && $password === $crypted) || ($this->crypt && $this->crypt->hash($password, $salt) === $crypted);
	}

	public function authenticate($app, $callback)
	{
		$isLoginUrl = $this->isLoginUrl($app);
		$isValid = $this->isValid($app);
		if (!$isValid) {
			if (!$isLoginUrl) {
				$this->writeRedirect($app, $app->url->requestUrl);
				$this->redirectToLogin($app);
			}
			if ($app->url->requestMethod === 'POST') {
				$userId = $app->post->{$this->options->loginFormUserId};
				$password = $app->post->{$this->options->loginFormPassword};
				if (is_null($callback)) {
					$user = $this->userProxy->getUserByLoginId($app, $this, $userId);
				} else {
					$user = call_user_func($callback, $userId);
				}
				$cryptedPassword = $user ? $user->{$this->options->columnPassword} : false ;
				$passwordSalt = $user ? $user->{$this->options->columnPasswordSalt} : '' ;
				if ($cryptedPassword && $this->isValidPassword($password, $cryptedPassword, $passwordSalt)) {
					$this->login($app, $user);
					$this->redirectAfterLogin($app);
				} else {
					$app->error->{$this->options->errorName} = true;
					$this->redirectToLogin($app);
				}
			}
		} else if ($isLoginUrl) {
			$this->redirectAfterLogin($app);
		}
	}

	public function redirectSessionKey($app)
	{
		return $this->options->sessionName . $this->options->postfix;
	}

	public function readRedirect($app)
	{
		$key = $this->redirectSessionKey($app);
		return $app->session->{$key};
	}

	public function writeRedirect($app, $value)
	{
		$key = $this->redirectSessionKey($app);
		$app->session->{$key} = $value;
		return $value;
	}

	public function deleteRedirect($app)
	{
		$key = $this->redirectSessionKey($app);
		$app->session->{$key} = null;
	}

	public function redirectToLogin($app)
	{
		$app->redirect($this->options->login);
	}

	public function redirectAfterLogin($app)
	{
		if ($this->options->force && !is_null($this->options->redirect)) {
			$app->redirect($this->options->redirect);
		} else {
			$redirect = $this->readRedirect($app);
			if($redirect && !$this->isLoginUrl($app, $redirect)) {
				$app->redirect($redirect);
			}
			if (!is_null($this->options->redirect)) {
				$app->redirect($this->options->redirect);
			}
			$app->redirect('/');
		}
	}

	public function logout($app)
	{
		parent::logout($app);
		$this->deleteRedirect($app);
	}
}
