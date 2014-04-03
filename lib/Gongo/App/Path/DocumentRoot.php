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
		return dirname($_SERVER['SCRIPT_FILENAME']);
	}

	function _assets()
	{
		return $this->html . Gongo_File_Path::make('/assets');
	}

	function _themes()
	{
		return $this->html . Gongo_File_Path::make('/themes');
	}

	function _css()
	{
		return $this->assets . Gongo_File_Path::make('/css');
	}

	function _js()
	{
		return $this->assets . Gongo_File_Path::make('/js');
	}

	function _img()
	{
		return $this->assets . Gongo_File_Path::make('/img');
	}
}
