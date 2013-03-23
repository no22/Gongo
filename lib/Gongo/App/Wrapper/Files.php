<?php
class Gongo_App_Wrapper_Files extends Gongo_Bean_ArrayWrapper 
{
	public function __construct() 
	{
		$this->_data = &$_FILES;
	}
}
