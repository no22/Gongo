<?php
interface Gongo_App_Auth_AutoLogin_MapperInterface 
{
	public function savePassport($app, $auth, $user, $passport);
	
	public function erasePassport($app, $auth, $user);

	public function updateLoginUser($app, $auth, $user); 
	
	public function getUserByLoginId($app, $auth, $loginName);
	
	public function getUserByPassport($app, $auth, $passport);
}
