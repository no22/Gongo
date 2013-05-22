<?php
class Gongo_Lock extends Gongo_Container
{
	public $uses = array(
		'-dir' => null,
		'-basename' => 'lockfile',
		'-timeout' => 1800,
		'-trytime' => 10,
		'-sleep' => 1,
	);

	protected $path = null;
	protected $current = null;

	function lock($dir = null, $basename = null, $timeout = null, $trytime = null, $sleep = null) 
	{
		$dir = is_null($dir) ? $this->options->dir : $dir ;
		$dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR ;
		$basename = is_null($basename) ? $this->options->basename : $basename ;
		$timeout = is_null($timeout) ? $this->options->timeout : $timeout ;
		$trytime = is_null($trytime) ? $this->options->trytime : $trytime ;
		$sleep = is_null($sleep) ? $this->options->sleep : $sleep ;
		$this->path = $dir . $basename ;
		for ($i = 0; $i < $trytime; $i++, sleep($sleep)) {
			if (rename($this->path, $this->current = $this->path . time())) {
				return true;
			}
		}
		$filelist = scandir($dir);
		foreach ($filelist as $file) {
			if (preg_match('/^' . preg_quote($basename, '/') . '(\d+)/', $file, $matches)) {
				if (((time() - $matches[1]) > $timeout) && rename($dir . $matches[0], $this->current = $this->path . time())) {
					return true;
				}
				break;
			}
		}
		return false;
	}

	function unlock() 
	{
		rename($this->current, $this->path);
	}
}
