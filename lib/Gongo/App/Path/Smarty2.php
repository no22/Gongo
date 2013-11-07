<?php
class Gongo_App_Path_Smarty2 extends Gongo_Component_Container
{
	protected $path;

	function __construct($path)
	{
		$this->path = $path;
		Gongo_Locator::load($this->libraryPath ,'Smarty');
	}

	function _libraryPath()
	{
		return $this->path->lib . Gongo_File_Path::make('/smarty2/Smarty.class.php');
	}

	function _templatePath()
	{
		return $this->path->template . Gongo_File_Path::make('/smarty2/');
	}

	function _configPath()
	{
		return $this->path->config . Gongo_File_Path::make('/smarty2/');
	}

	function _cachePath()
	{
		if (!$this->path->temp) return false;
		$path = $this->path->temp . Gongo_File_Path::make('/smarty2/cache/');
		Gongo_File::makeDir($path);
		return $path;
	}

	function _compiledTemplatePath()
	{
		if (!$this->path->temp) return false;
		$path = $this->path->temp . Gongo_File_Path::make('/smarty2/templates_c/');
		Gongo_File::makeDir($path);
		return $path;
	}
}