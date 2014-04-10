<?php
class Gongo_Crypt_Hash extends Gongo_Crypt_Base
{
	protected $algo = 'sha512';
	
	function __construct($salt = null, $algo = null)
	{
		if (!is_null($salt)) $this->salt($salt);
		if (!is_null($algo)) $this->algo($algo);
	}

	function algo($value = null)
	{
		if (is_null($value)) return $this->algo;
		$this->algo = $value;
		return $this;
	}
	
	function hash($text, $salt = '', $raw = false)
	{
		return hash($this->algo(), $text . $this->salt() . $salt, $raw);
	}
}
