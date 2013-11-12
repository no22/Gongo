<?php
class Gongo_Builder_GoogleAppEngine extends Gongo_Builder
{
	function build_Gongo_App_Path($root)
	{
		return new Gongo_App_Path_GoogleAppEngine($root);
	}

	function build_Gongo_App_Auth_Basic($data = array())
	{
		return new Gongo_App_Auth_Basic_Cgi($data);
	}
}
