<?php
class Gongo_Crypt_Md5 extends Gongo_Crypt_Base
{
	function hash($text, $salt = '', $raw = false)
	{
		return md5($text . $this->salt() . $salt, $raw);
	}
}
