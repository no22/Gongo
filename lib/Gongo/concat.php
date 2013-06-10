<?php
define('GONGO_LIB_ROOT', dirname(__FILE__));
define('GONGO_ROOT', dirname(GONGO_LIB_ROOT));
require GONGO_LIB_ROOT . '/Autoload.php';
define('OUTPUT', GONGO_LIB_ROOT . '/core.php');
define('CLASSES', GONGO_LIB_ROOT . '/corelib.txt');
define('HEADER', GONGO_LIB_ROOT . '/coreheader.php');

function concatClass($class, $output, $libroot, $included = array())
{
	$className = trim($class);
	if (!$className) return $included;
	if (strpos($className, 'Gongo_') !== 0) return $included;
	if (isset($included[$className])) return $included;
	$classFile = strtr($className, array('_' => DIRECTORY_SEPARATOR)).'.php';
	$classPath = $libroot . DIRECTORY_SEPARATOR . $classFile;
	if (!file_exists($classPath)) return $included;
	$included[$className] = true;
	echo $className ."\n";
	$script = file_get_contents($classPath);
	$script = preg_replace('/^<\?php/s', '', $script);
	if (preg_match_all('/\s+extends\s+(Gongo_\w+)/', $script, $m)) {
		foreach ($m[1] as $baseClass) {
			if (isset($included[$baseClass])) continue;
			echo "+ " . $baseClass ."\n";
			$included = concatClass($baseClass, $output, $libroot, $included);
		}
	}
	file_put_contents($output, $script, FILE_APPEND);
	return $included;
}

$included = array();
$script = file_get_contents(HEADER);
file_put_contents(OUTPUT, $script);
$classes = new SplFileObject(CLASSES);

foreach ($classes as $class) {
	$included = concatClass($class, OUTPUT, GONGO_ROOT, $included);
}
