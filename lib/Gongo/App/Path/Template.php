<?php
class Gongo_App_Path_Template extends Gongo_Component_Container
{
	protected $path;
	protected $name;
	
	function __construct($path, $name = 'php')
	{
		$this->path = $path;
		$this->name = $name;
		$this->init();
	}
	
	function init()
	{
		
	}
	
	function templatePath($name = null)
	{
		$name = is_null($name) ? $this->name : $name ;
		return $this->path->template . Gongo_File_Path::make('/' . $name . '/');
	}

	function cachePath($name = null)
	{
		$name = is_null($name) ? $this->name : $name ;
		$path = $this->path->temp . Gongo_File_Path::make('/' . $name . '/');
		Gongo_File::makeDir($path);
		return $path;
	}

	function _templatePath()
	{
		return $this->templatePath();
	}

	function _cachePath()
	{
		return $this->cachePath();
	}
}