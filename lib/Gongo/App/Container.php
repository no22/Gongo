<?php
class Gongo_App_Container extends Gongo_Bean
{
	function __construct($data = array())
	{
		$this->__();
		self::merge($this, $data);
	}
}
