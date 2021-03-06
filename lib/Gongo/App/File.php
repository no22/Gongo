<?php
class Gongo_App_File extends Gongo_App_Base
{
	public function mimeContentType($path)
	{
		if (PHP_VERSION_ID > 50300) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$contentType = finfo_file($finfo, $path);
			finfo_close($finfo);
		} else {
			$contentType = mime_content_type($path);
		}
		return $contentType;
	}
	
	public function send($path, $filename = null, $contentType = null, $type = 'inline', $nosniff = true)
	{
		$filename = is_null($filename) ? Gongo_File_Path::basename($path) : $filename ;
		$contentType = is_null($contentType) ? $this->mimeContentType($path) : $contentType ;
		if ($nosniff) header('X-Content-Type-Options: nosniff');
		header("Content-type: {$contentType}");
		header("Content-Disposition: {$type}; filename={$filename}");
		header("Content-Length: " . filesize($path));
		readfile($path);
		exit();
	}

	public function download($path, $filename = null, $nosniff = true) 
	{
		$filename = is_null($filename) ? Gongo_File_Path::basename($path) : $filename ;
		if ($nosniff) header('X-Content-Type-Options: nosniff');
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename={$filename}");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($path));
		readfile($path);
		exit();
	}
}
