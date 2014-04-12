<?php
class Gongo_App_Bean_Converter extends Gongo_App_Base
{
	public $uses = array(
		'-rule' => array(
		),
		'-fields' => null,
		'-enable' => null,
		'-disable' => null,
		'-arrayFilter' => false,
		'-currentArrayFilter' => false,
		'-functions' => array(
			'trim', 'ltrim', 'rtrim', 'md5', 'sha1', 'nl2br', 'mb_convert_kana',
			'intval', 'strval', 'floatval', 'strtoupper', 'strtolower',
			'bin2hex', 'hex2bin','convert_uudecode', 'convert_uuencode',
			'strip_tags', 'ucfirst', 'ucwords',
		),
		'-unset' => false,
		'-break' => false,
	);

	public function expandFilters($filters)
	{
		$expanded = array();
		foreach ($filters as $key => $rule) {
			$this->options->currentArrayFilter = $this->options->arrayFilter;
			$rules = is_string($rule) ? array_map('trim', explode('|', $rule)) : $rule ;
			foreach ($rules as $filter) {
				$expandedRule = array();
				if (is_array($filter)) {
					$type = array_shift($filter);
					$expandedRule['type'] = $type;
					$expandedRule['params'] = $filter;
				} else if (is_string($filter)) {
					$expandedRule['type'] = $filter;
				}
				$expanded[$key][] = $expandedRule;
			}
		}
		$fields = array_keys($expanded);
		return array($expanded, $fields);
	}

	public function convert($src, $dst, $filters = null, $cast = true, $strict = false, $unset = true)
	{
		if ($cast) $dst = Gongo_Bean::cast($dst, $src, $strict, $unset);
		$filters = is_null($filters) ? $this->options->rule : $filters ;
		if (empty($filters)) return $dst;
		list($expanded, $fields) = $this->expandFilters($filters);
		if (!is_null($this->enable)) {
			$fields = $this->enable;
		}
		if (!is_null($this->disable)) {
			$fields = array_diff($fields, $this->disable);
		}
		foreach ($expanded as $key => $filter) {
			if (in_array($key, $fields)) {
				$current = $cast ? $dst->{$key} : $src->{$key} ;
				$this->options->break = false;
				foreach ($filter as $f) {
					$this->options->unset = false;
					$method = $f['type'];
					$params = isset($f['params']) ? $f['params'] : null ;
					if (is_null($dst)) continue;
					if (is_string($method)) {
						if (in_array($method, $this->options->functions)) {
							$current = $this->exec($method, $current, $src, $dst, $key, $params);
						} else if (method_exists($this, $method . 'Handler')) {
							$current = $this->{$method . 'Handler'}($current, $src , $dst, $key, $params);
						} else if (method_exists($this, $method)) {
							$current = $this->exec(array($this, $method), $current, $src, $dst, $key, $params);
						}
					} else if (is_callable($method)) {
						$current = call_user_func($method, $current, $src, $dst, $key, $params);
					}
					if ($this->options->break) break;
				}
			}
			if (!$this->options->unset) {
				$dst->{$key} = $current;
			}
		}
		return $dst;
	}

	public function inputFilter($app, $src = null)
	{
		$src = is_null($src) ? $app->post : $src ;
		$this->convert($src, $src, null, false);
	}

	public function execrec($callback, $value, $params = null)
	{
		$array = $this->options->currentArrayFilter;
		if (!$array || !is_array($value)) {
			$args = array($value);
			if ($params) $args = array_merge($args, $params);
			return call_user_func_array($callback, $args);
		} else {
			$newValue = array();
			foreach ($value as $k => $v) {
				$args = array($v);
				if ($params) $args = array_merge($args, $params);
				$newValue[$k] = call_user_func_array($callback, $args);
			}
			return $newValue;
		}
	}

	public function exec($callback, $current, $src, $dst, $key, $params = null)
	{
		return $this->execrec($callback, $current, $params);
	}

	protected function breakProcess($break = true)
	{
		$this->options->break = $break;
	}

	protected function unsetAttr($dst, $key, $break = true)
	{
		unset($dst->{$key});
		$this->options->unset = true;
		$this->breakProcess($break);
		return null;
	}

	public function identity($value)
	{
		return $value;
	}

	public function value($value, $newValue)
	{
		return $newValue;
	}

	public function clearHandler($current, $src, $dst, $key, $params = null)
	{
		$value = $dst->{$key};
		if (is_int($value)) {
			$current = 0;
		} else if (is_float($value)) {
			$current = 0.0;
		} else if (is_string($value)) {
			$current = '';
		} else if (is_bool($value)) {
			$current = false;
		} else {
			$current = null;
		}
		return $current;
	}

	public function unsetHandler($current, $src, $dst, $key, $params = null)
	{
		return $this->unsetAttr($dst, $key);
	}

	public function nullIfEmpty($value)
	{
		return $value ? $value : null ;
	}

	public function emptyIfNull($value)
	{
		return is_null($value) ? '' : $value ;
	}

	public function zeroIfEmpty($value)
	{
		return $value ? $value : 0 ;
	}

	public function emptyIfZero($value)
	{
		return $value == 0 ? '' : $value ;
	}

	public function unsetIfEmptyHandler($current, $src, $dst, $key, $params = null)
	{
		if ($current) return $current;
		return $this->unsetAttr($dst, $key);
	}

	public function mbTrim($value)
	{
		return Gongo_Str::trim($value);
	}

	public function space($value)
	{
		return Gongo_Str::replaceSpaces($value);
	}

	public function zenkakukana($value)
	{
		return mb_convert_kana($value, 'KV');
	}

	public function hankakukana($value)
	{
		return mb_convert_kana($value, 'k');
	}

	public function zenkaku($value)
	{
		return mb_convert_kana($value, 'AS');
	}

	public function hankaku($value)
	{
		return mb_convert_kana($value, 'as');
	}

	public function uft8($value)
	{
		return mb_convert_encoding($value, 'utf-8');
	}

	public function encoding($value, $to, $from)
	{
		return mb_convert_encoding($value, $to, $from);
	}

	public function explode($value)
	{
		return array_map('trim', explode(',', $value));
	}

	public function implode($value)
	{
		if (!$value) return '';
		return implode(',', $value);
	}

	public function arrayEnable($value)
	{
		$this->options->currentArrayFilter = true;
		return $value;
	}

	public function arrayDisable($value)
	{
		$this->options->currentArrayFilter = false;
		return $value;
	}
}
