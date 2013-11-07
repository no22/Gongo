<?php
class Gongo_Builder_GoogleAppEngine extends Gongo_Builder
{
	function build_Gongo_App_Path($root)
	{
		return new Gongo_App_Path_GoogleAppEngine($root);
	}
}
