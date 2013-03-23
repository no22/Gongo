<?php
class Gongo_App_Path_Doo extends Gongo_App_Path_Template
{	
	function init()
	{
		Gongo_Locator::load($this->libraryPath ,'Doo');
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/dwoo/dwooAutoload.php');
	}
}
