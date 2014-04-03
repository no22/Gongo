<?php
interface Gongo_App_File_SystemInterface
{
	public function read($name);
	public function write($name, $contents);
	public function append($name, $contents);
	public function exists($name);
	public function getList($name);
	public function delete($name);
	public function cp($src, $dst);
	public function mv($src, $dst);
	public function rename($old, $new);
	public function isDir($name);
	public function isFile($name);
	public function mtime($name);
	public function size($name);
	public function mkdir($name);
	public function clearStatCache($name);
	public function moveUploadedFile($uploadedFile, $filepath);
}
