<?php
class Gongo_App_Ajax_JSONHelper extends Gongo_App_Base
{
	function encode($data)
	{
		return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
	}
}
