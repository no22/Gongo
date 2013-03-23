<?php
class Gongo_App_Cache_Http extends Gongo_App_Base
{
	function header($timestamp, $maxAge = 28800) 
	{
		header("Cache-Control: private, must-revalidate, max-age={$maxAge}");
		header('Expires: Thu, 01-Jan-70 00:00:01 GMT');
		header('Pragma: cache');
		$tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
		$etag = $timestamp;
		$if_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
		$if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;
		if ((($if_none_match && $if_none_match == $etag) || (!$if_none_match)) && ($if_modified_since && $if_modified_since == $tsstring)) {
			header('HTTP/1.1 304 Not Modified');
			exit();
		}
		header("Last-Modified: {$tsstring}");
		header("ETag: \"{$etag}\"");
	}
}
