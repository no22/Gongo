<?php
class Gongo_Crypt_Hash extends Gongo_Crypt_Base
{
	protected $algo = 'tiger128,3';
	
	function __construct($salt = '', $algo = 'tiger128,3')
	{
		$this->salt($salt);
		$this->algo($algo);
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
