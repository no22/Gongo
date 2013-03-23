<?php
class Gongo_File_Text
{
	protected $path;
	protected $text;
	
	public function __construct($path)
	{
		$this->path = $path;
		if (file_exists($path)) {
			$this->text = file_get_contents($path);
		}
	}
	
	public function replace($regex, $rep)
	{
		$this->text = preg_replace($regex, $rep, $this->text);
		return $this;
	}
	
	public function save($path = null)
	{
		$path = is_null($path) ? $this->path : $path ;
		file_put_contents($path, $this->text);
		return $this;
	}
}
