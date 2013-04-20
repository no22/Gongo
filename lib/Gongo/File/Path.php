<?php
class Gongo_File_Path
{
	static function make($path)
	{
		$aPath = explode('/', $path);
		return implode(DIRECTORY_SEPARATOR, $aPath);
	}

	static function preparePaths($paths, $root = '')
	{
		$paths = !is_array($paths) ? explode(',', $paths) : $paths ;
		$paths = array_filter(array_map('trim', $paths));
		$absPaths = array();
		foreach ($paths as $path) {
			$rpath = self::make($root . '/' . $path);
			$apath = realpath($rpath);
			
			$absPaths[] = $apath ? $apath : $rpath ;
		}
		return $absPaths;
	}

	static function absolutePath($path, $root = null)
	{
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			if ('.' == $part) continue;
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		return (is_null($root) ? DIRECTORY_SEPARATOR : $root) . implode(DIRECTORY_SEPARATOR, $absolutes);
	}
}
