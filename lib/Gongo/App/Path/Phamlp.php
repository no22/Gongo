<?php
class Gongo_App_Path_Phamlp extends Gongo_App_Path_Template
{	
	function init()
	{
		Gongo_Locator::load($this->libraryPath ,'HamlParser');
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/phamlp/haml/HamlParser.php');
	}
}
