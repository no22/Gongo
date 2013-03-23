<?php
class Gongo_File
{
	static function makeDir($path, $mode = 0777, $recursive = true)
	{
		if (file_exists($path)) return $path;
		if (!mkdir($path, $mode, $recursive)) {
			throw new Gongo_File_Exception("failed to create " . $path);
		}
		return $path;
	}

	static function dirSize($path, $readable = false) 
	{
		$option = $readable ? '-sh' : '-sb' ;
		$result = exec('du ' . $option . ' ' . $path);
		if (preg_match('/^([^\s]+)\s/', trim($result), $match)) {
			return $match[1];
		}
		return false;
	}

	static function rmDir($path, $delete = true, $top = null) 
	{
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		$top = is_null($top) ? $path : $top ;
		$handle = opendir($path);
		if (!$handle) return false;
		while (false !== ($file = readdir($handle))) {
			if ($file === '.' || $file === '..') continue;
			$target = $path . DIRECTORY_SEPARATOR . $file;
			if (is_file($target)) {
				unlink($target);
			} else if (is_dir($target)) {
				self::rmDir($target, $delete, $top);
			}
		}
		if ($path !== $top) rmdir($path);
		if ($path === $top && $delete) rmdir($top);
		return true;
	}
}
