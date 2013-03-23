<?php
class Gongo_App_Wrapper_Session extends Gongo_Bean_ArrayWrapper
{
	public function __construct()
	{
		$this->_data = &$_SESSION;
	}

	public function __set($key, $value)
	{
		if (is_null($value)) {
			unset($this->_data[$key]);
			return $value;
		} else {
			$this->_data[$key] = $value;
			return $value;
		}
	}
}
