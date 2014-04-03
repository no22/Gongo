<?php
class Gongo_App_File_System_Local extends Gongo_Container implements Gongo_App_File_SystemInterface
{
	public $uses = array(
		'-dirmode' => 0777,
	);

	public function read($name)
	{
		return file_get_contents($name);
	}

	public function write($name, $contents)
	{
		return file_put_contents($name, $contents);
	}

	public function append($name, $contents)
	{
		return file_put_contents($name, $contents, FILE_APPEND | LOCK_EX);
	}

	public function exists($name)
	{
		return file_exists($name);
	}

	public function getList($name)
	{
		return Gongo_File::scandir($name);
	}

	public function delete($name)
	{
		return unlink($name);
	}

	public function cp($src, $dst)
	{
		return copy($src, $dst);
	}

	public function mv($src, $dst)
	{
		return rename($src, $dst);
	}

	public function rename($old, $new)
	{
		return $this->mv($old, $new);
	}

	public function isDir($name)
	{
		return is_dir($name);
	}

	public function isFile($name)
	{
		return is_file($name);
	}

	public function mtime($name)
	{
		return filemtime($name);
	}

	public function size($name)
	{
		return filesize($name);
	}

	public function mkdir($name)
	{
		return mkdir($name, $this->options->dirmode, true);
	}

	public function clearStatCache($name = null)
	{
		if ($name && version_compare(PHP_VERSION, '5.3.0') >= 0) {
			return clearstatcache(true, $name);
		} else {
			return clearstatcache();
		}
	}

	public function moveUploadedFile($uploadedFile, $filepath)
	{
		return move_uploaded_file($uploadedFile, $filepath);
	}
}
