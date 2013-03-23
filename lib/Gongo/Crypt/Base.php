<?php
class Gongo_Crypt_Base
{
	protected $salt = '';
	
	function __construct($salt = null)
	{
		if (!is_null($salt)) $this->salt($salt);
	}
	
	function salt($value = null)
	{
		if (is_null($value)) return $this->salt;
		$this->salt = $value;
		return $this;
	}

	function uniqid($prefix = '', $more = true, $salt = '', $raw = false)
	{
		return $this->hash(uniqid($prefix, $more), $salt, $raw);
	}
}
