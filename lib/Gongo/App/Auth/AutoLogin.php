<?php
class Gongo_App_Auth_AutoLogin extends Gongo_App_Auth
{	
	public $uses = array(
		'-passportName' => 'passport',
		'-passportExpire' => 604800,
		'-passportPath' => null,
		'-passportDomain' => null,
		'-passportSecure' => false,
		'-passportHttpOnly' => false,
		'-formAutologin' => 'autologin',
		'-errorName' => 'loginError',
	);

	public function newPassport($app)
	{
		return md5(uniqid(mt_rand(), true));
	}
	
	public function setPassport($app, $sPassport = null, $aCookieOptions = array())
	{
		$sPassport = is_null($sPassport) ? $this->newPassport($app) : $sPassport ;
		$sCookiePath = $this->options->passportPath;
		$sCookiePath = $sCookiePath ? $sCookiePath : Gongo_App::$environment->path->mountPoint . '/' ;
		$aCookie = array(
			$sPassport, 
			time() + $this->options->passportExpire,
			$sCookiePath,
			$this->options->passportDomain,
			$this->options->passportSecure,
			$this->options->passportHttpOnly,
		);
		$aCookie = array_merge($aCookie, $aCookieOptions);
		$app->cookie->{$this->options->passportName} = $aCookie;
		return $sPassport;
	}

	public function getPassport($app)
	{
		return $app->cookie->{$this->options->passportName};
	}

	public function delPassport($app)
	{
		$sCookiePath = $this->options->passportPath;
		$sCookiePath = $sCookiePath ? $sCookiePath : Gongo_App::$environment->path->mountPoint . '/' ;
		$aCookie = array(
			'', 
			time() - 3600,
			$sCookiePath,
			$this->options->passportDomain,
			$this->options->passportSecure,
			$this->options->passportHttpOnly,
		);
		$app->cookie->{$this->options->passportName} = $aCookie;
		return null;
	}

	public function createPassport($app)
	{
		return $this->setPassport($app);
	}

	public function savePassport($app, $user, $passport)
	{
		return $this->userProxy->savePassport($app, $this, $user, $passport);
	}

	public function erasePassport($app)
	{
		$user = $this->readLoginUser($app);
		if (!$user) return;
		return $this->userProxy->erasePassport($app, $this, $user);
	}
	
	public function isValid($app)
	{
		$user = $this->readLoginUser($app);
		$this->isValid = !empty($user);
		if ($this->isValid) return $this->isValid;
		$passport = $this->getPassport($app);
		$user = $this->userProxy->getUserByPassport($app, $this, $passport);
		if (!$user) return false;
		$this->isValid = true;
		$this->login($app, $user);
		$this->writeLoginUser($app, $user);
		return true;
	}

	public function processLogin($app, $user)
	{
		if ($user) {
			$user = $this->updateLoginUser($app, $user);
			$this->setNewPassport($app, $user);
		}
		return $user;
	}
	
	public function logout($app)
	{
		$this->erasePassport($app);
		$this->delPassport($app);
		parent::logout($app);
	}

	public function setNewPassport($app, $user)
	{
		$this->delPassport($app);
		if ($app->url->requestMethod !== 'POST' || $app->post->{$this->options->formAutologin}) {
			$newPassport = $this->createPassport($app);
			$this->savePassport($app, $user, $newPassport);
		} else {
			$this->erasePassport($app);
		}
	}

	public function updateLoginUser($app, $user)
	{
		return $this->userProxy->updateLoginUser($app, $this, $user);
	}
	
}
