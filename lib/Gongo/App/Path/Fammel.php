<?php
class Gongo_App_Path_Fammel extends Gongo_App_Path_Template
{	
	function init()
	{
		Gongo_Locator::load($this->libraryPath ,'Fammel');
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/Fammel/fammel.php');
	}
}
