<?php
class Gongo_File_Ini extends Gongo_File_Text
{
	public $attr = null;
	
	public function __construct($path)
	{
		parent::__construct($path);
		if (file_exists($path)) {
			$attr = $this->parse();
			if ($attr) $this->attr = $attr;
		}
	}
	
	public function parse()
	{
		return Gongo_Locator::get('Gongo_Bean', parse_ini_file($this->path ,true));
	}
	
	public function attr($value = null)
	{
		if (is_null($value)) return $this->attr;
		$this->attr = $value;
		return $this;
	}
	
	protected function getValue($cfg)
	{
		$value = isset($cfg['value']) ? $cfg['value'] : null ;
		if (is_null($value)) return '';
		if (is_bool($value)) return $value ? 'true' : 'false' ;
		if (is_int($value)||is_float($value)) return $value;
		if (is_string($value)) return '"' . $value . '"';
		if (is_array($value)) return '"' . implode(',', $value) . '"';
		return $value;
	}
	
	protected function getAttr($value, $strToArr = false)
	{
		if ($value === '') return null;
		if (strtolower($value) === 'false') return false ;
		if (strtolower($value) === 'true') return true ;
		if (preg_match('/^\d+$/u', $value)) return (int) $value ;
		if (preg_match('/^\d*\.\d+$/u', $value)) return (float) $value ;
		if (preg_match('/^"([^"]*)"$/u', $value, $m)) {
			if (!$strToArr) return $m[1];
			return array_map('trim', explode(',', $m[1]));
		}
		return $value;
	}
	
	protected function addValue($key, $cfg, $newBuff)
	{
		$type = isset($cfg['type']) ? array_map('trim', explode('&', $cfg['type'])) : array() ;
		if (!in_array('delete', $type)) {
			if (isset($cfg['comment'])) {
				$newBuff[] = ';' . $cfg['comment'];
			}
			$newBuff[] = $key . " = " . $this->getValue($cfg);
		}
		return $newBuff;
	}

	protected function updateValue($key, $value, $cfg, $newBuff, $line)
	{
		$type = isset($cfg['type']) ? array_map('trim', explode('&', $cfg['type'])) : array() ;
		if (in_array('overwrite', $type)) {
			$newValue = $this->getValue($cfg);
			if ($newValue !== $value) {
				if (in_array('commentout', $type)) {
					$newBuff[] = ';' . $line;
				}
				$newBuff[] = $key . " = " . $this->getValue($cfg);
			} else {
				$newBuff[] = $line;
			}
		} else if (in_array('delete', $type)) {
			if (in_array('commentout', $type)) {
				$newBuff[] = ';' . $line;
			}
			// do nothing
		} else if (in_array('merge', $type)) {
			$oldAttr = $this->getAttr($value, true);
			$addValue = !is_array($cfg['value']) ? array_map('trim',explode(',', $cfg['value'])) : $cfg['value'] ;
			$diff = array_diff($addValue, $oldAttr);
			if (!empty($diff)) {
				if (in_array('commentout', $type)) {
					$newBuff[] = ';' . $line;
				}
				$newAttr = array_merge($oldAttr, $diff);
				$newValue = '"' . implode(',', $newAttr) . '"';
				$newBuff[] = $key . " = " . $newValue;
			} else {
				$newBuff[] = $line;
			}
		} else {
			$newBuff[] = $line;
		}
		return $newBuff;
	}
	
	protected function appendValue($section, $config, $newBuff)
	{
		if ($section === '') {
			foreach ($config as $k => $cfg) {
				if (!preg_match('/^\[[^\]]+\]$/u', $k)) {
					$newBuff = $this->addValue($k, $cfg, $newBuff);
					unset($config[$k]);
				}
			}
		} else if (isset($config[$section])) {
			foreach ($config[$section] as $k => $cfg) {
				$newBuff = $this->addValue($k, $cfg, $newBuff);
				unset($config[$section][$k]);
			}
			unset($config[$section]);
		}
		return array($newBuff, $config);
	}
	
	public function update($config = array())
	{
		$buff = explode("\n", $this->text);
		$newBuff = array();
		$section = '';
		
		foreach ($buff as $line) {
			if (preg_match('/^\s*(\[[^\]]+\])/u', $line, $m)) {
				// 新しい属性値の追記
				list($newBuff, $config) = $this->appendValue($section, $config, $newBuff);
				// 新しいセクションの開始
				$section = $m[1];
				$newBuff[] = $line;
			} else if (preg_match('/^\s*([^\s=]+)\s*=\s*(.*)\s*$/u', $line, $m)) {
				$key = $m[1];
				$value = $m[2];
				if ($section !== '' && isset($config[$section][$key])) {
					$newBuff = $this->updateValue($key, $value, $config[$section][$key], $newBuff, $line);
					unset($config[$section][$key]);
				} else if ($section === '' && isset($config[$key])) {
					$newBuff = $this->updateValue($key, $value, $config[$key], $newBuff, $line);
					unset($config[$key]);
				} else {
					$newBuff[] = $line;
				}
			} else {
				$newBuff[] = $line;
			}
		}
		// ループ終了
		list($newBuff, $config) = $this->appendValue($section, $config, $newBuff);
		foreach ($config as $section => $sec) {
			if (preg_match('/^\[[^\]]+\]$/u', $section)) {
				$newBuff[] = $section;
				foreach ($sec as $key => $cfg) {
					$newBuff = $this->addValue($key, $cfg, $newBuff);
					unset($config[$section][$key]);
				}
				unset($config[$section]);
			}
		}
		$this->text = implode("\n", $newBuff);
		return $this;
	}
}
