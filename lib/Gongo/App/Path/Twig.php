<?php
class Gongo_App_Path_Twig extends Gongo_Component_Container
{
	protected $path;

	function __construct($path)
	{
		Twig_Autoloader::register();
		$this->path = $path;
	}

	function _templatePath()
	{
		return $this->path->template . Gongo_File_Path::make('/twig/');
	}

	function _cachePath()
	{
		if (!$this->path->temp) return false;
		$path = $this->path->temp . Gongo_File_Path::make('/twig/');
		Gongo_File::makeDir($path);
		return $path;
	}
}