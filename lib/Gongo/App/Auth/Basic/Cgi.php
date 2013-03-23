<?php
class Gongo_App_Auth_Basic_Cgi extends Gongo_App_Auth_Basic
{
	public function getHttpAuthInfo($app)
	{
		return explode(':', base64_decode(substr($app->server->HTTP_AUTHORIZATION, 6)));
	}
}
