<?php
//!count(debug_backtrace()) and require dirname(__FILE__) . "/../../gongo.php";
/**
 * Gongo_Str_Base
 * 
 * @package		Gongo
 * @version		1.0.0
 */ 
class Gongo_Str_Base 
{
	protected $regexEncoding = 'UTF-8';
	protected $emailValidator = null;
			
	/**
	 * init
	 * @param string $sEncoding
	 */
	public function init($encoding = null)
	{
		$encoding = is_null($encoding) ? $this->regexEncoding : $encoding ;
		mb_regex_encoding($encoding);
	}

	/**
	 * emailValidator
	 * 
	 * @return object
	 */
	public function emailValidator()
	{
		if (is_null($this->emailValidator)) {
			$this->emailValidator = Gongo_Locator::get('Gongo_Str_Email');
		}
		return $this->emailValidator;
	}
	
	/**
	 * startsWith
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->startsWith('abc', 'ab'), true);
	 * eq($o->startsWith('abc', 'bc'), false);
	 * <<<<
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public function startsWith($haystack, $needle)
	{
		return strpos($haystack, $needle, 0) === 0;
	}

	/**
	 * endsWith
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->endsWith('abc', 'ab'), false);
	 * eq($o->endsWith('abc', 'bc'), true);
	 * <<<<
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public function endsWith($haystack, $needle)
	{
		$length = (strlen($haystack) - strlen($needle));
		if ($length < 0) { return false; }
		return strpos($haystack, $needle, $length) !== false;
	}

	/**
	 * matchesIn
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->matchesIn('abc', 'ab'), true);
	 * eq($o->matchesIn('abc', 'bc'), true);
	 * <<<<
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public function matchesIn($haystack, $needle)
	{
		return strpos($haystack, $needle) !== false;
	}
	
	/**
	 * trim
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->trim("  　abc　　  \n"), 'abc');
	 * <<<<
	 * @param string $text
	 * @return string
	 */
	public function trim($text)
	{
		return preg_replace('/\A[\0\s]+|[\0\s]+\z/u', '', $text);
	}

	/**
	 * date
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->date('2013-1-28 10:07:01'), '2013-01-28 10:07:01');
	 * <<<<
	 * @param string $text
	 * @param string $sFormat
	 * @return string
	 */
	public function date($text, $sFormat = 'Y-m-d H:i:s')
	{
		$utTime = strtotime($text);
		return date($sFormat, $utTime);
	}
	
	/**
	 * split
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->split('ab , cd , ef'), array('ab','cd','ef'));
	 * eq($o->split(array('ab','cd','ef')), array('ab','cd','ef'));
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return array
	 */
	public function split($text, $delim = ',')
	{
		if (!$text) $text = array();
		return !is_string($text) ? $text : array_filter(array_map('trim', explode($delim, $text))) ;
	}

	/**
	 * replaceSpaces
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->replaceSpaces('ab 　cd　ef     ggg'), 'ab cd ef ggg');
	 * <<<<
	 * @param string $text
	 * @param  string $replacement
	 * @return string
	 */
	public function replaceSpaces($text, $replacement = ' ')
	{
		return preg_replace('/\s+/u', $replacement, $text);
	}

	/**
	 * lcfirst
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->lcfirst('FooBarBaz'), 'fooBarBaz');
	 * <<<<
	 * @param string $text
	 * @return string
	 */
	public function lcfirst($text)
	{
		return strtolower(substr($text,0,1)) . substr($text,1);
	}

	/**
	 * email
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->email('foo@example.com'), true);
	 * <<<<
	 * @param  string $email
	 * @return bool
	 */
	public function email($email, $strict = true)
	{
		return $this->emailValidator()->isValid($email, $strict);
	}

	/**
	 * lowerSnakeToUpperSnake
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->lowerSnakeToUpperSnake('abc_def_ghi'), 'Abc_Def_Ghi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function lowerSnakeToUpperSnake($text, $delim = '_')
	{
		return strtr(ucwords(strtr($text, array($delim => ' '))), array(' ' => $delim));
	}
	
	/**
	 * snakeToPascal
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->snakeToPascal('abc_def_ghi'), 'AbcDefGhi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function snakeToPascal($text, $delim = '_')
	{
		return strtr(ucwords(strtr($text, array($delim => ' '))), array(' ' => ''));
	}

	/**
	 * snakeToCamel
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->snakeToCamel('abc_def_ghi'), 'abcDefGhi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function snakeToCamel($text, $delim = '_')
	{
		return $this->lcfirst($this->snakeToPascal($text, $delim));
	}

	/**
	 * pascalToUpperSnake
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->pascalToUpperSnake('AbcDefGhi'), 'Abc_Def_Ghi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function pascalToUpperSnake($text, $delim = '_')
	{
		return preg_replace('/([a-z])([A-Z])/', "$1$delim$2", $text);
	}

	/**
	 * camelToUpperSnake
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->camelToUpperSnake('abcDefGhi'), 'Abc_Def_Ghi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function camelToUpperSnake($text, $delim = '_')
	{
		return ucfirst($this->pascalToUpperSnake($text, $delim));
	}

	/**
	 * pascalToSnake
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->pascalToSnake('AbcDefGhi'), 'abc_def_ghi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function pascalToSnake($text, $delim = '_')
	{
		return strtolower($this->pascalToUpperSnake($text, $delim));
	}

	/**
	 * camelToSnake
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->camelToSnake('abcDefGhi'), 'abc_def_ghi');
	 * <<<<
	 * @param string $text
	 * @param  string $delim
	 * @return string
	 */
	public function camelToSnake($text, $delim = '_')
	{
		return strtolower($this->pascalToUpperSnake($text, $delim));
	}

	/**
	 * baseConvert
	 * >>>>
	 * $o = new Gongo_Str_Base;
	 * eq($o->baseConvert('255', 10, 2), '11111111');
	 * <<<<
	 * @param string $text
	 * @param int $from
	 * @param int $to
	 * @param string $chars
	 * @return string
	 */
	public function baseConvert($text, $from, $to, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-')
	{
		if (max($to, $from) > strlen($chars)) return false;
		if (min($to, $from) < 2) return false;
		$value = trim((string)$text);
		$sign = $value{0} === '-' ? '-' : '' ;
		$value = ltrim($value, '0-+');
		$len = strlen($value);
		$decimal = '0';
		for ($i = 0; $i < $len; $i++) {
			$n = strpos($chars, $value{$len-$i-1});
			if ($n === false) return false;
			if ($n >= $from) return false;
			$decimal = bcadd($decimal, bcmul(bcpow($from, $i), $n));
		}
		if ($to === 10) return $sign . $decimal;
		$level = 0;
		while(1 !== bccomp(bcpow($to, $level++), $decimal));
		$result = '';
		for ($i = $level-2; $i >= 0; $i--) {
			$factor = bcpow($to, $i);
			$division = bcdiv($decimal, $factor, 0);
			$decimal = bcmod($decimal, $factor);
			$result .= $chars{$division};
		}
		return empty($result) ? '0' : $sign . $result ;
	}
}

//!count(debug_backtrace()) and Sloth::doctest(__FILE__);
