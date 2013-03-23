<?php
class Gongo_App_Wrapper_Request extends Gongo_Bean_ArrayWrapper 
{
	public function __construct() 
	{
		$this->_data = &$_REQUEST;
	}
}
