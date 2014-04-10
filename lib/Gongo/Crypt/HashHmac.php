<?php
class Gongo_Crypt_HashHmac extends Gongo_Crypt_Hash
{
	protected $key = "";
	
	function __construct($salt = null, $algo = null, $key = null)
	{
		if (!is_null($salt)) $this->salt($salt);
		if (!is_null($algo)) $this->algo($algo);
		if (!is_null($key)) $this->key($key);
	}

	function key($value = null)
	{
		if (is_null($value)) return $this->key;
		$this->key = $value;
		return $this;
	}
	
	function hash($text, $salt = '', $key = '', $raw = false)
	{
		$key = $key ? $key : $this->key() ;
		return hash_hmac($this->algo(), $text . $salt . $this->salt(), $key, $raw);
	}
}
