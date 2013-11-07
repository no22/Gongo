<?php
class Gongo_App_Path_GoogleAppEngine extends Gongo_App_Path
{
	function _temp()
	{
		$environmentVariable = 'GONGO_TEMP_DIR';
		$tempdir = isset($_SERVER[$environmentVariable]) ? $_SERVER[$environmentVariable] : false ;
		if (!$tempdir) $tempdir = getenv($environmentVariable);
		return $tempdir;
	}
}
