<?php
class Gongo_App_Path_Mustache extends Gongo_App_Path_Template
{	
	function init()
	{
		//Gongo_Locator::load($this->libraryPath ,'Mustache_Engine', true);
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/Mustache/Autoloader.php');
	}
}
