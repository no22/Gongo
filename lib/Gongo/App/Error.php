<?php
class Gongo_App_Error extends Gongo_App_Container
{
	public function err($key, $delim = '<br />')
	{
		if (!isset($this->errors[$key])) return '';
		$message = array();
		foreach ($this->errors[$key] as $err) {
			$message[] = $err['message'];
		}
		return implode($delim, $message);
	}
}
