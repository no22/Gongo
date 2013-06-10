<?php
//!count(debug_backtrace()) and require dirname(__FILE__) . "/../../gongo.php";
/**
 * Gongo_Str_Compat
 * 
 * @package		
 * @version		1.0.0	
 */ 
class Gongo_Str_Compat extends Gongo_Str_Base 
{
	public function __construct($encoding = null)
	{
		if (!is_null($encoding)) $this->regexEncoding = $encoding;
		$this->init($this->regexEncoding);
	}

	/**
	 * trim
	 * >>>>
	 * $o = new Gongo_Str_Compat;
	 * eq($o->trim("  　abc　　  \n"), 'abc');
	 * <<<<
	 * @param string $text
	 * @return string
	 */
	public function trim($text)
	{
		return mb_ereg_replace('(\A[\0\s]+|[\0\s]+\z)', '', $text);
	}

	/**
	 * replaceSpaces
	 * >>>>
	 * $o = new Gongo_Str_Compat;
	 * eq($o->replaceSpaces('ab 　cd　ef     ggg'), 'ab cd ef ggg');
	 * <<<<
	 * @param string $text
	 * @param  string $replacement
	 * @return string
	 */
	public function replaceSpaces($text, $replacement = ' ')
	{
		return mb_ereg_replace('\s+', $replacement, $text);
	}
}

//!count(debug_backtrace()) and Sloth::doctest(__FILE__);
