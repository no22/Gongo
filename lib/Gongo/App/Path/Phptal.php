<?php
class Gongo_App_Path_Phptal extends Gongo_App_Path_Template
{	
	function init()
	{
		Gongo_Locator::load($this->libraryPath ,'PHPTAL');
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/PHPTAL/PHPTAL.php');
	}
}
