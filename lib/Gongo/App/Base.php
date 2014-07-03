<?php
class Gongo_App_Base extends Gongo_Container
{
	static protected $cfg = null;

	static public function cfg($oCfg = null)
	{
		if (is_null($oCfg)) return self::$cfg;
		self::$cfg = $oCfg;
	}

	public function __get($sName)
	{
		if ($sName[0] === '_') {
			if ($sName[1] !== '_') {
				return $this->factory->getObj('Gongo_Container_Promise', $this, substr($sName, 1));
			}
			$sName = substr($sName, 2);
			return isset($this->components[$sName]) || isset($this->components['/'.$sName]) ;
		} else if (isset($this->components[$sName])) {
			return $this->{$sName} = call_user_func($this->components[$sName]);
		} else if (isset($this->components['/'.$sName])) {
			return $this->{$sName} = call_user_func($this->components['/'.$sName]);
		}
		return null;
	}
}
