<?php
class Gongo_Crypt_Sha1 extends Gongo_Crypt_Base
{
	function hash($text, $salt = '', $raw = false)
	{
		return sha1($text . $this->salt() . $salt, $raw);
	}
}
