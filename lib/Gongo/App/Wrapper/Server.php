<?php
class Gongo_App_Wrapper_Server extends Gongo_Bean_ArrayWrapper 
{
	public function __construct() 
	{
		$this->_data = &$_SERVER;
	}
}
