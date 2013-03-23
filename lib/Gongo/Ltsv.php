<?php
/**
 * Gongo_Ltsv
 * 
 * @package		Gongo
 * @version		1.0.0
 * @authur		Hiroyuki OHARA <Hiroyuki.no22@gmail.com>
 * @create		2013-02-11
 */ 
class Gongo_Ltsv
{
	protected $wants = array();
	protected $ignores = array();
	
	function ignoreFields($keys = null) 
	{
		if (is_null($keys)) return $this->ignores;
		$this->ignores = $keys;
		return $this;
	}
	
	function wantFields($keys = null) 
	{
		if (is_null($keys)) return $this->wants;
		$this->wants = $keys;
		return $this;
	}
	
	function hasIgnores() 
	{
		return !empty($this->ignores);
	}
	
	function hasWants() 
	{
		return !empty($this->wants);
	}
	
	function parseLine($line) 
	{
		$wants = $this->wants;
		$ignores = $this->ignores;
		$hasWants = $this->hasWants();
		$hasIgnores = $this->hasIgnores();
		$kv = array();
		foreach (explode("\t", rtrim($line, "\n")) as $_kv) {
			$item = explode(':', $_kv, 2);
			if ($hasIgnores && in_array($item[0], $ignores)) continue;
			if ($hasWants && !in_array($item[0], $wants)) continue;
			$kv[$item[0]] = isset($item[1]) ? $item[1] : null ;
		}
		return $kv;
	}
	
	function parseFile($path) 
	{
		$out = array();
		foreach (new SplFileObject($path) as $line) {
			$out[] = $this->parseLine($line);
		}
		return $out;
	}
	
	function parseFileIter($path) 
	{
		return Sloth::iter(new SplFileObject($path))->map(array($this, 'parseLine'));
	}
}
