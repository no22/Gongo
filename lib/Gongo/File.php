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

	static function mv($src, $dst)
	{
		return rename($src, $dst);
	}

	static function rm($path, $terminate = true)
	{
		$path = is_array($path) ? $path : array($path) ;
		foreach ($path as $file) {
			if (is_file($file)) {
				if (!unlink($file) && $terminate) return false;
			} else if (is_dir($file)) {
				if (!self::rmDir($file) && $terminate) return false;
			}
		}
		return true;
	}

	static function cpDir($src, $dst, $overwrite = false)
	{
		$success = true;
		if (!file_exists($src)) return false;
		if (!file_exists($dst)) self::makeDir($dst);
		if (!is_dir($dst)) return false;
		$dstpath = rtrim($dst, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Gongo_File_Path::basename($src);
		if (!$overwrite && file_exists($dstpath)) return false;
		if (is_file($src)) {
			if (!copy($src, $dstpath)) return false;
		} else if (is_dir($src)) {
			self::makeDir($dstpath);
			$src = rtrim($src, DIRECTORY_SEPARATOR);
			foreach (scandir($src) as $filename) {
				if ($filename === '.' || $filename === '..') continue;
				if ($filename[0] === '.') continue;
				if (!self::cpDir($src . DIRECTORY_SEPARATOR . $filename, $dstpath, $overwrite)) $success = false;
			}
		}
		return $success;
	}

	static function mvDir($src, $dst, $overwrite = false)
	{
		$success = true;
		if (!file_exists($src)) return false;
		if (!file_exists($dst)) self::makeDir($dst);
		if (!is_dir($dst)) return false;
		$dstpath = rtrim($dst, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Gongo_File_Path::basename($src);
		if (!$overwrite && file_exists($dstpath)) return false;
		if (is_file($src)) {
			if (!self::mv($src, $dstpath)) return false;
		} else if (is_dir($src)) {
			self::makeDir($dstpath);
			$src = rtrim($src, DIRECTORY_SEPARATOR);
			foreach (scandir($src) as $filename) {
				if ($filename === '.' || $filename === '..') continue;
				if ($filename[0] === '.') continue;
				if (!self::mvDir($src . DIRECTORY_SEPARATOR . $filename, $dstpath, $overwrite)) $success = false;
			}
			self::rmDir($src);
		}
		return $success;
	}

	static function iter($path)
	{
		return Sloth::iter(Gongo_Locator::get('DirectoryIterator', $path));
	}

	static function files($path, $files, $sort = "name")
	{
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		$result = array();
		$isName = $sort === 'name';
		foreach ($files as $name) {
			$filepath = $path . DIRECTORY_SEPARATOR . $name ;
			$stat = stat($filepath);
			$value = $isName ? $name : $stat[$sort] ;
			if ($sort === 'size' && is_dir($filepath)) $value = 0;
			$result[$name] = $value ;
		}
		return $result;
	}

	static function scandir($path, $order = 0, $sort = 'name', $context = null)
	{
		$files = is_null($context) ? scandir($path, $order) : scandir($path, $order, $context) ;
		if ($sort !== 'name') {
			$files = self::files($path, $files, $sort);
			if ($order) {
				arsort($files);
			} else {
				asort($files);
			}
			$files = array_keys($files);
		}
		return $files;
	}

	static function scanDirIter($path, $order = 0, $sort = 'name', $context = null)
	{
		$files = self::scandir($path, $order, $sort, $context);
		return Sloth::iter($files);
	}

	static function readableSize($size, $round = 1, $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB'))
	{
		$mod = 1024;
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		return round($size, $round) . ' ' . $units[$i];
	}
}
