<?php
class Gongo_App_Path_DocumentRoot extends Gongo_Component_Container
{
	protected $path;
	
	function __construct($path)
	{
		$this->path = $path;
	}
	
	function _html()
	{
		return $this->path->home . Gongo_File_Path::make('/html');
	}

	function _css()
	{
		return $this->html . Gongo_File_Path::make('/css');
	}

	function _js()
	{
		return $this->html . Gongo_File_Path::make('/js');
	}

	function _img()
	{
		return $this->html . Gongo_File_Path::make('/img');
	}
}
