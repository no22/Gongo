<?php

mb_internal_encoding('UTF-8');

if (!function_exists('getallheaders')) {
	function getallheaders()
	{
		$headers = array();
		foreach($_SERVER as $k => $v) {
			if(ereg('HTTP_(.+)', $k, $kp)) {
				$headers[$kp[1]] = $v;
			}
		}
		return $headers;
	}
}
