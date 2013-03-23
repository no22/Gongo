<?php
class Gongo_App_Token_Secure extends Gongo_App_Token
{
	public $uses = array(
		'crypt' => 'Gongo_Crypt_Md5',
		'-tokenName' => '__token__',
		'-errorRedirect' => '/error',
		'-sessionName' => 'secureToken',
	);
	
	public function token()
	{
		$sessionName = $this->options->sessionName;
		$sessionToken = isset($_SESSION[$sessionName]) ? $_SESSION[$sessionName] : null ;
		if ($sessionToken) return $sessionToken;
		$_SESSION[$sessionName] = $this->crypt->hash(uniqid(mt_rand(), true));
		return $_SESSION[$sessionName];
	}
}
