<?php
class Gongo_Str_Email_Keitai extends Gongo_Str_Email
{
	public function isValid($email, $strict = false)
	{
		return parent::isValid($email, $strict);
	}
}
