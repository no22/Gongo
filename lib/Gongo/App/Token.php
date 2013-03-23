<?php
class Gongo_App_Token extends Gongo_App_Base
{
	public $uses = array(
		'crypt' => 'Gongo_Crypt_Md5',
		'-tokenName' => '__token__',
		'-errorRedirect' => '/error',
	);
	
	public function token()
	{
		return $this->crypt->hash(session_id());
	}
	
	public function read($app)
	{
		return $this->token();
	}

	public function isValidPost($app, $fnCallback)
	{
		$sPostToken = $app->post->{$this->options->tokenName};
		$sToken = $this->read($app);
		if ($sPostToken) unset($app->post->{$this->options->tokenName});
		if (is_null($sPostToken) || $sPostToken === '' || $sPostToken !== $sToken) {
			if ($fnCallback) {
				return call_user_func($fnCallback, $app);
			}
			return $app->redirect($this->options->errorRedirect);
		}
	}

	public function isValidGet($app, $fnCallback)
	{
		$sGetToken = $app->get->{$this->options->tokenName};
		$sToken = $this->read($app);
		if ($sGetToken) unset($app->get->{$this->options->tokenName});
		if (is_null($sGetToken) || $sGetToken === '' || $sGetToken !== $sToken) {
			if ($fnCallback) {
				return call_user_func($fnCallback, $app);
			}
			return $app->redirect($this->options->errorRedirect);
		}
	}
}
