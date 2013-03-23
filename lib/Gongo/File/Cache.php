<?php
class Gongo_File_CacheException extends Exception {}

class Gongo_File_Cache
{
	function exists($filepath, $cachepath) 
	{
		if (!is_file($cachepath)) return false;
		return filemtime($cachepath) > filemtime($filepath);
	}
	
	function update($filepath, $cachepath, $callback = null, $permission = 0777)
	{
		if ($this->exists($filepath, $cachepath)) return false;
		$content = file_get_contents($filepath);
		if (!is_null($callback)) {
			$content = call_user_func($callback, $content);
		}
		$cachedir = dirname($cachepath);
		if(!is_dir($cachedir)) {
			mkdir($cachedir, $permission, true);
		}
		return file_put_contents($cachepath, $content, LOCK_EX);
	}

	function updateFile($filepath, $cachepath, $callback = null, $permission = 0777)
	{
		if ($this->exists($filepath, $cachepath)) return false;
		$cachedir = dirname($cachepath);
		if(!is_dir($cachedir)) {
			mkdir($cachedir, $permission, true);
		}
		if (!is_null($callback)) {
			return call_user_func($callback, $filepath, $cachepath);
		}
		return file_put_contents($cachepath, file_get_contents($filepath), LOCK_EX);
	}
}
