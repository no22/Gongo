<?php
!count(debug_backtrace()) and require dirname(__FILE__) . "/../gongo.php";
/**
 * Gongo_Str
 * 
 * @package		
 * @version		1.0.0
 */ 
class Gongo_Str
{
	static $singleton = null;
	
	static function getInstance()
	{
		if (is_null(self::$singleton)) {
			self::$singleton = Gongo_Locator::get('Gongo_Str_Base');
		}
		return self::$singleton;
	}
	
	/**
	 * init
	 * 
	 * @param string $sEncoding
	 * @return 
	 */
	static function init($sEncoding = "UTF-8")
	{
		$obj = self::getInstance();
		return $obj->init($sEncoding);
	}
	
	/**
	 * startsWith
	 * >>>>
	 * eq(Gongo_Str::startsWith('abc', 'ab'), true);
	 * eq(Gongo_Str::startsWith('abc', 'bc'), false);
	 * <<<<
	 * @param string $sHaystack
	 * @param string $sNeedle
	 * @return bool
	 */
	static function startsWith($sHaystack, $sNeedle)
	{
		$obj = self::getInstance();
		return $obj->startsWith($sHaystack, $sNeedle);
	}

	/**
	 * endsWith
	 * >>>>
	 * eq(Gongo_Str::endsWith('abc', 'ab'), false);
	 * eq(Gongo_Str::endsWith('abc', 'bc'), true);
	 * <<<<
	 * @param string $sHaystack
	 * @param string $sNeedle
	 * @return bool
	 */
	static function endsWith($sHaystack, $sNeedle)
	{
		$obj = self::getInstance();
		return $obj->endsWith($sHaystack, $sNeedle);
	}

	/**
	 * matchesIn
	 * >>>>
	 * eq(Gongo_Str::matchesIn('abc', 'ab'), true);
	 * eq(Gongo_Str::matchesIn('abc', 'bc'), true);
	 * <<<<
	 * @param string $sHaystack
	 * @param string $sNeedle
	 * @return bool
	 */
	static function matchesIn($sHaystack, $sNeedle)
	{
		$obj = self::getInstance();
		return $obj->matchesIn($sHaystack, $sNeedle);
	}
	
	/**
	 * trim
	 * >>>>
	 * eq(Gongo_Str::trim("  　abc　　  \n"), 'abc');
	 * <<<<
	 * @param string $sText
	 * @return string
	 */
	static function trim($sText)
	{
		$obj = self::getInstance();
		return $obj->trim($sText);
	}

	/**
	 * date
	 * >>>>
	 * eq(Gongo_Str::date('2013-1-28 10:07:01'), '2013-01-28 10:07:01');
	 * <<<<
	 * @param string $sText
	 * @param string $sFormat
	 * @return string
	 */
	static function date($sText, $sFormat = 'Y-m-d H:i:s')
	{
		$obj = self::getInstance();
		return $obj->date($sText, $sFormat);
	}
	
	/**
	 * split
	 * >>>>
	 * eq(Gongo_Str::split('ab , cd , ef'), array('ab','cd','ef'));
	 * eq(Gongo_Str::split(array('ab','cd','ef')), array('ab','cd','ef'));
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return array
	 */
	static function split($text, $delim = ',')
	{
		$obj = self::getInstance();
		return $obj->split($text, $delim);
	}

	/**
	 * replaceSpaces
	 * >>>>
	 * eq(Gongo_Str::replaceSpaces('ab 　cd　ef     ggg'), 'ab cd ef ggg');
	 * <<<<
	 * @param string $text
	 * @param  string $replacement
	 * @return string
	 */
	static function replaceSpaces($text, $replacement = ' ')
	{
		$obj = self::getInstance();
		return $obj->replaceSpaces($text, $replacement);
	}

	/**
	 * lcfirst
	 * >>>>
	 * eq(Gongo_Str::lcfirst('FooBarBaz'), 'fooBarBaz');
	 * <<<<
	 * @param string $text
	 * @return string
	 */
	static function lcfirst($text)
	{
		$obj = self::getInstance();
		return $obj->lcfirst($text);
	}

	/**
	 * email
	 * >>>>
	 * eq(Gongo_Str::email('foo.bar@example.com'), true);
	 * <<<<
	 * @param  $email
	 * @return bool
	 */
	static function email($email, $strict = true)
	{
		$obj = self::getInstance();
		return $obj->email($email, $strict);
	}

	/**
	 * lowerSnakeToUpperSnake
	 * >>>>
	 * eq(Gongo_Str::lowerSnakeToUpperSnake('abc_def_ghi'), 'Abc_Def_Ghi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function lowerSnakeToUpperSnake($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->lowerSnakeToUpperSnake($text, $delim);
	}
	
	/**
	 * snakeToPascal
	 * >>>>
	 * eq(Gongo_Str::snakeToPascal('abc_def_ghi'), 'AbcDefGhi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function snakeToPascal($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->snakeToPascal($text, $delim);
	}

	/**
	 * snakeToCamel
	 * >>>>
	 * eq(Gongo_Str::snakeToCamel('abc_def_ghi'), 'abcDefGhi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function snakeToCamel($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->snakeToCamel($text, $delim);
	}

	/**
	 * pascalToUpperSnake
	 * >>>>
	 * eq(Gongo_Str::pascalToUpperSnake('AbcDefGhi'), 'Abc_Def_Ghi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function pascalToUpperSnake($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->pascalToUpperSnake($text, $delim);
	}

	/**
	 * camelToUpperSnake
	 * >>>>
	 * eq(Gongo_Str::camelToUpperSnake('abcDefGhi'), 'Abc_Def_Ghi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function camelToUpperSnake($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->camelToUpperSnake($text, $delim);
	}

	/**
	 * pascalToSnake
	 * >>>>
	 * eq(Gongo_Str::pascalToSnake('AbcDefGhi'), 'abc_def_ghi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function pascalToSnake($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->pascalToSnake($text, $delim);
	}

	/**
	 * camelToSnake
	 * >>>>
	 * eq(Gongo_Str::camelToSnake('abcDefGhi'), 'abc_def_ghi');
	 * <<<<
	 * @param string $text
	 * @param  $delim
	 * @return string
	 */
	static function camelToSnake($text, $delim = '_')
	{
		$obj = self::getInstance();
		return $obj->camelToSnake($text, $delim );
	}

	/**
	 * baseConvert
	 * >>>>
	 * eq(Gongo_Str::baseConvert('255', 10, 2), '11111111');
	 * <<<<
	 * @param string $text
	 * @param int $from
	 * @param int $to
	 * @param string $chars
	 * @return string
	 */
	static function baseConvert($text, $from, $to, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-')
	{
		$obj = self::getInstance();
		return $obj->baseConvert($text, $from, $to, $chars);
	}
}

!count(debug_backtrace()) and Sloth::doctest(__FILE__);
