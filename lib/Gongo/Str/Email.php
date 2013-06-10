<?php
//!count(debug_backtrace()) and require dirname(__FILE__) . "/../../gongo.php";
/**
 * Gongo_Str_Email
 * 
 * @package		
 * @version		1.0.0	
 */ 
class Gongo_Str_Email
{
	public function isValid($email, $strict = true)
	{
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) return false;
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64) return false;
		if ($domainLen < 1 || $domainLen > 255) return false;
		if ($local[0] === '.') return false;
		if ($local[$localLen-1] === '.' && $strict) return false;
		if (strpos($local,'..') !== false && ($local[0] !== '"' || $local[$localLen-1] !== '"') && $strict) {
			return false;
		}
		if (strpos($domain,'.') === false && $domain !== 'localhost') return false;
		if (!preg_match('/\A[A-Za-z0-9\\-\\.]+\z/', $domain)) return false;
		if (strpos($domain,'..') !== false) return false;
		if ($domain !== 'localhost' && (strlen(substr($domain, strrpos($domain, '.')+1)) < 2 || strlen(substr($domain, strrpos($domain, '.')+1)) > 6)) {
			return false;
		}
		if (!preg_match('/\A(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+\z/', str_replace("\\\\","",$local))) {
			if (!preg_match('/\A"(\\\\"|[^"])+"\z/', str_replace("\\\\","",$local))) {
				return false;
			}
		}
		return true;
	}
}

//!count(debug_backtrace()) and Sloth::doctest(__FILE__);

/**
 * >>>>
 * $aMethod = array(
 * 	'new', 
 * );
 * $strict = true;
 * $aMail = array(
 * 	"dclo@us.ibm.com" => 1, "abc\\@def@example.com" => 1, "abc\\\\@example.com" => 1,
 * 	"Fred\\ Bloggs@example.com" => 1, "Joe.\\\\Blow@example.com" => 1, "\"Abc@def\"@example.com" => 1,
 * 	"\"Fred Bloggs\"@example.com" => 1, "customer/department=shipping@example.com" => 1, 
 * 	"\$A12345@example.com" => 1, "!def!xyz%abc@example.com" => 1, "_somename@example.com" => 1,
 * 	"user+mailbox@example.com" => 1, "peter.piper@example.com" => 1, "Doug\\ \\\"Ace\\\"\\ Lovell@example.com" => 1,
 * 	"\"Doug \\\"Ace\\\" L.\"@example.com" => 1, '"abc@def"@example.com' => 1, '"Fred \"quota\" Bloggs"@example.com' => 1,
 * 	'da..me.@docomo.ne.jp' => 1, 'da..me.@ezweb.ne.jp' => 1, '"da..me."@docomo.ne.jp' => 1, '"da..me."@ezweb.ne.jp' => 1,
 * 	"joe@localhost" => 1, "joe@[192.168.24.70]" => 1, 'aaa@bbb.jp' => 1, '"two..dot"@example.com' => 1,
 * 	"user+mailbox/department=shipping@example.com" => 1, "!#$%&'*+-/=?^_`.{|}~@example.com" => 1,
 * 	"abc@def@example.com" => 0, "abc\\\\@def@example.com" => 0, "abc\\@example.com" => 0, "@example.com" => 0,
 * 	"doug@" => 0, "\"qu@example.com" => 0, "ote\"@example.com" => 0, ".dot@example.com" => 0,
 * 	"dot.@example.com" => 0, "two..dot@example.com" => 0, "\"Doug \"Ace\" L.\"@example.com" => 0, 
 * 	"Doug\\ \\\"Ace\\\"\\ L\\.@example.com" => 0, "hello world@example.com" => 0, "gatsby@f.sc.ot.t.f.i.tzg.era.l.d." => 0,
 * 	"joe@aaa" => 0, "joe@192.168.24.70" => 0, "joe@aaa.j" => 0, "ばka@manuke.com" => 0, "da.me..@docomo.ne.jp" => 0,
 *  "dankogai+regexp@gmail.com" => 1, "dankogai+regexp@gmail.com\n" => 0,
 * );
 * //ob_start();
 * foreach ($aMethod as $sMethod) {
 * 	$o = new Gongo_Str_Email();
 * 	$iAll = 0;
 * 	$iSucc = 0;
 * 	foreach ($aMail as $sAddress => $bSucc) {
 * 		if ($o->isValid($sAddress, $strict)) {
 * 			$sSucc = $bSucc ? 'Success!' : 'Failed!!' ;
 * 			echo "\t{$sSucc}\t{$sAddress}\t:\tValid.\n";
 * 		}
 * 		else {
 * 			$sSucc = $bSucc ? 'Failed!!' : 'Success!' ;
 * 			echo "\t{$sSucc}\t{$sAddress}\t:\tInvalid\n";
 * 		}
 * 		$iAll++;
 * 		if ($sSucc === 'Success!') {
 * 			$iSucc++;
 * 		}
 * 	}
 * 	echo "$sMethod : {$iSucc}/{$iAll}\n";
 * }
 * //$sOut = ob_get_contents();
 * //ob_end_clean();
 * //$oFile = new SimpleFile(MWDATAPATH.'mwmail_address_log.txt','w');
 * //$oFile->open();
 * //$oFile->write($sOut);
 * //$oFile->close();
 * <<<<
 */
