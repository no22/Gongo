<?php
class Gongo_Builder_AppFog extends Gongo_Builder
{
	function build_Gongo_App_DB_PDO_Builder()
	{
		return new Gongo_App_DB_PDO_Builder_AppFog();
	}
}
