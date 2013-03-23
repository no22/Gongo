<?php
class Gongo_App_Wrapper_Cookie extends Gongo_Bean_ArrayWrapper
{
	public function __construct() 
	{
		$this->_data = &$_COOKIE;
	}

	public function __set($key, $value) 
	{
		if (is_null($value)) {
			setcookie($key, '', time() - 3600);
			unset($this->_data[$key]);
			return $value;
		} else if (is_string($value)) {
			setcookie($key, $value);
			$this->_data[$key] = $value;
			return $value;
		} else if (is_array($value)) {
			@list($val, $expire, $path, $domain, $secure, $httponly) = $value;
			$expire = is_null($expire) ? 0 : $expire ;
			$secure = is_null($secure) ? false : $secure ;
			if (PHP_VERSION_ID > 50200) {
				$httponly = is_null($httponly) ? false : $httponly ;
				setcookie($key, $val, $expire, $path, $domain, $secure, $httponly);
			} else {
				setcookie($key, $val, $expire, $path, $domain, $secure);
			}
			$this->_data[$key] = $val;
			return $val;
		}
		return null;
	}
}
