<?php
class Gongo_App_Path_H2o extends Gongo_App_Path_Template
{	
	function init()
	{
		Gongo_Locator::load($this->libraryPath ,'h2o');
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/h2o/h2o.php');
	}
}
