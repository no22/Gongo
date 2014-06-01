<?php
class Gongo_App_Validator extends Gongo_App_Base
{
	public $uses = array(
		'-rules' => null,
		'-errorName' => 'validationError',
		'-action' => array('submit'),
		'-redirect' => null,
		'-defaultErrorMessage' => 'validation error',
		'emailValidator' => 'Gongo_Str_Email',
		'errors' => 'Gongo_App_Error',
	);

	public $fields = null;
	public $enable = null;
	public $disable = null;

	public function postData($app, $mData)
	{
		if (is_null($mData)) {
			return $app->post->_();
		} else if (is_array($mData)) {
			return $mData;
		} else if ($mData instanceof Gongo_Bean) {
			return $mData->_();
		} else if (is_object($mData)) {
			return (array) $mData;
		}
		return false;
	}

	public function validate($app, $mData = null)
	{
		$mData = $this->postData($app, $mData);
		if ($mData) return $this->validateArray($mData);
		return false;
	}

	public function expandRules()
	{
		$expanded = array();
		$aDefaultRule = array(
			'tag' => 'error', 'type' => 'required', 'params' => null, 'message' => $this->options->defaultErrorMessage,
		);
		foreach ($this->options->rules as $tag => $rule) {
			$keys = array_shift($rule);
			$keys = explode(',', $keys);
			foreach ($keys as $key) {
				$key = trim($key);
				$expandedRule = array();
				if (is_string($tag)) {
					$expandedRule['tag'] = $tag;
				}
				foreach ($rule as $k => $value) {
					if ($k === 0) {
						$expandedRule['type'] = $value;
					} else if ($k === 'type') {
						$expandedRule['type'] = $value;
					} else if ($k === 'params') {
						$expandedRule['params'] = $value;
					} else if ($k === 'message') {
						$expandedRule['message'] = $value;
					} else {
						$expandedRule['type'] = $k;
						$expandedRule['params'] = $value;
					}
				}
				$expanded[$key][] = array_merge($aDefaultRule, $expandedRule);
			}
		}
		$this->fields = array_keys($expanded);
		return $expanded;
	}

	public function execHandler($method, $aData, $key, $r)
	{
		$result = false;
		if (is_string($method)) {
			if (method_exists($this, $method . 'Handler')) {
				$result = $this->{$method . 'Handler'}($aData , $key, $r);
			} else if (method_exists($this, $method)) {
				$result = $this->exec(array($this, $method), $aData , $key, $r);
			}
		} else if (is_callable($method)) {
			$result = call_user_func($method, $aData , $key, $r);
		}
		return $result;
	}

	public function validateArray($aData)
	{
		$rules = $this->expandRules();
		$fields = $this->fields;
		if (!is_null($this->enable)) {
			$fields = $this->enable;
		}
		if (!is_null($this->disable)) {
			$fields = array_diff($fields, $this->disable);
		}
		$errors = array();
		foreach ($rules as $key => $rule) {
			if (in_array($key, $fields)) {
				foreach ($rule as $r) {
					$result = $this->execHandler($r['type'], $aData, $key, $r);
					if ($result) {
						$errors[$key][] = $result;
					}
				}
			}
		}
		if (!empty($errors)) {
			$errorArray = array($aData, $errors);
			$this->errors->errors = $errors;
			$this->errors->postdata = $aData;
			return $errorArray;
		}
		return false;
	}

	public function execute($app, $mData, $fnCallback)
	{
		$mData = $this->postData($app, $mData);
		$actions = is_string($this->options->action) ? array_filter(array_map('trim', explode(',', $this->options->action))) : $this->options->action ;
		$action = strtolower($app->dispatcher->submitName($mData));

		if (!$action || !in_array($action, $actions)) return;

		if (is_null($this->options->redirect)) {
			$this->options->redirect = $app->url->requestUrl;
		}
		if ($fnCallback) {
			$newData = call_user_func($fnCallback, $app, $this, $mData);
			if (!is_null($newData)) {
				$mData = $newData;
			}
		}
		$errors = $this->validate($app, $mData);
		if ($errors) {
			if ($this->options->errorName) {
				$app->error->{$this->options->errorName} = $this->errors;
			}
			if ($this->options->redirect) {
				$app->redirect($this->options->redirect);
			}
		}
	}

	public function err($key, $delim = '<br />')
	{
		if (!isset($this->errors[$key])) return '';
		$message = array();
		foreach ($this->errors[$key] as $err) {
			$message[] = $err['message'];
		}
		return implode($delim, $message);
	}

	public function exec($callback, $aData, $key, $rule)
	{
		$result = isset($aData[$key]) ? call_user_func($callback, $aData[$key], $rule['params']) : false ;
		return $result ? null : array('tag' => $rule['tag'], 'message' => $rule['message']) ;
	}

	public function required($value, $params)
	{
		return true;
	}

	public function notEmpty($value, $params)
	{
		return $value != '';
	}

	public function isEmpty($value, $params)
	{
		return $value == '';
	}

	public function notEmptyIfIDExistsHandler($aData, $key, $rule)
	{
		$colName = is_null($rule['params']) ? 'id' : $rule['params'] ;
		if (!isset($aData[$colName]) || $aData[$colName] == '') return null;
		if (isset($aData[$key]) && $aData[$key] != '') {
			return null;
		}
		return array('tag' => $rule['tag'], 'message' => $rule['message']);
	}

	public function notEmptyIfIDNotExistsHandler($aData, $key, $rule)
	{
		$colName = is_null($rule['params']) ? 'id' : $rule['params'] ;
		if (isset($aData[$colName]) && $aData[$colName] != '') return null;
		if (isset($aData[$key]) && $aData[$key] != '') {
			return null;
		}
		return array('tag' => $rule['tag'], 'message' => $rule['message']);
	}

	public function notBlank($value, $params)
	{
		$text = Gongo_Str::trim($value);
		return $text != '';
	}

	public function matchHandler($aData, $key, $rule)
	{
		$value = isset($aData[$key]) ? $aData[$key] : '' ;
		if (preg_match($rule['params'], $value)) {
			return null;
		}
		return array('tag' => $rule['tag'], 'message' => $rule['message']);
	}

	public function notMatchHandler($aData, $key, $rule)
	{
		$value = isset($aData[$key]) ? $aData[$key] : '' ;
		if (!preg_match($rule['params'], $value)) {
			return null;
		}
		return array('tag' => $rule['tag'], 'message' => $rule['message']);
	}

	public function minLength($value, $params)
	{
		return strlen($value) >= $params;
	}

	public function maxLength($value, $params)
	{
		return strlen($value) <= $params;
	}

	public function mbMinLength($value, $params)
	{
		return mb_strlen($value) >= $params;
	}

	public function mbMaxLength($value, $params)
	{
		return mb_strlen($value) <= $params;
	}

	public function between($value, $params)
	{
		list($min, $max) = $params;
		$len = strlen($value);
		return $min <= $len && $len <= $max;
	}

	public function mbBetween($value, $params)
	{
		list($min, $max) = $params;
		$len = mb_strlen($value);
		return $min <= $len && $len <= $max;
	}

	public function range($value, $params)
	{
		list($min, $max) = $params;
		return $min <= $value && $value <= $max;
	}

	public function in($value, $params)
	{
		return in_array($value, $params);
	}

	public function datetime($value, $params)
	{
		return strtotime($value) !== false;
	}

	public function dateYMD($value, $params)
	{
		$ymd = strtr($value, array('-'=>'', '/'=>'', '.'=>''));
		if (strlen($ymd) === 8) {
			$y = substr($ymd, 0, 4);
			$m = substr($ymd, 4, 2);
			$d = substr($ymd, 6, 2);
			if (checkdate((int)$m, (int)$d, (int)$y)) {
				return true;
			}
		}
		return false;
	}

	public function dateMD($value, $params)
	{
		$md = strtr($value, array('-'=>'', '/'=>'', '.'=>''));
		if (strlen($md) === 4) {
			$m = substr($md, 0, 2);
			$d = substr($md, 2, 2);
			$days = array(
				1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
				7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31,
			);
			if (isset($days[(int)$m]) && (1 <= $d && $d <= $days[(int)$m])) {
				return true;
			}
		}
		return false;
	}

	public function timeHMS($value, $params)
	{
		$hms = strtr($value, array(':'=>''));
		if (strlen($hms) === 6) {
			$h = (int) substr($hms, 0, 2);
			$m = (int) substr($hms, 2, 4);
			$s = (int) substr($hms, 4, 6);
			if (0 <= $h && $h <= 23 && 0 <= $m && $m <= 59 && 0 <= $s && $s <= 59) {
				return true;
			}
		}
		return false;
	}

	public function timeHM($value, $params)
	{
		$hms = strtr($value, array(':'=>''));
		if (strlen($hms) === 4) {
			$h = (int) substr($hms, 0, 2);
			$m = (int) substr($hms, 2, 4);
			if (0 <= $h && $h <= 23 && 0 <= $m && $m <= 59) {
				return true;
			}
		}
		return false;
	}

	public function eitherHandler($aData, $key, $rule)
	{
		if (isset($aData[$key])) {
			$formdata = $aData[$key];
			foreach ($rule['params'] as $idx => $value) {
				if (is_int($idx)) {
					$r = array(
						'tag' => $rule['tag'], 'type' => $value, 'params' => null, 'message' => $rule['message'],
					);
					$result = $this->execHandler($value, $aData, $key, $r);
					if (is_null($result)) return null;
				} else {
					$r = array(
						'tag' => $rule['tag'], 'type' => $idx, 'params' => $value, 'message' => $rule['message'],
					);
					$result = $this->execHandler($idx, $aData, $key, $r);
					if (is_null($result)) return null;
				}
			}
		}
		return array('tag' => $rule['tag'], 'message' => $rule['message']);
	}

	public function notHandler($aData, $key, $rule)
	{
		if (isset($aData[$key])) {
			$formdata = $aData[$key];
			foreach ($rule['params'] as $idx => $value) {
				if (is_int($idx)) {
					$r = array(
						'tag' => $rule['tag'], 'type' => $value, 'params' => null, 'message' => $rule['message'],
					);
					$result = $this->execHandler($value, $aData, $key, $r);
					if (is_null($result)) return array('tag' => $rule['tag'], 'message' => $rule['message']);
				} else {
					$r = array(
						'tag' => $rule['tag'], 'type' => $idx, 'params' => $value, 'message' => $rule['message'],
					);
					$result = $this->execHandler($idx, $aData, $key, $r);
					if (is_null($result)) return array('tag' => $rule['tag'], 'message' => $rule['message']);
				}
			}
		}
		return null;
	}

	public function email($value, $params = null)
	{
		return $this->emailValidator->isValid($value);
	}

	public function url($value, $params = null)
	{
		return filter_var($value, FILTER_VALIDATE_URL);
	}

	public function ip($value, $params = null)
	{
		return filter_var($value, FILTER_VALIDATE_IP);
	}

	public function regexp($value, $params = null)
	{
		return filter_var($value, FILTER_VALIDATE_REGEXP);
	}

	public function bool($value, $params = null)
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	public function int($value, $params = null)
	{
		return filter_var($value, FILTER_VALIDATE_INT);
	}

	public function float($value, $params = null)
	{
		return filter_var($value, FILTER_VALIDATE_FLOAT);
	}
}
