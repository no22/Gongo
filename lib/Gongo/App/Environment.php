<?php
class Gongo_App_Environment extends Gongo_Component_Container
{
	static $env = null;

	static function get($root, $regen = false)
	{
		if (is_null(self::$env) || $regen) {
			self::$env = Gongo_Locator::get('Gongo_App_Environment', $root);
		}
		return self::$env;
	}

	static function read($key, $default = null)
	{
		if (isset($_SERVER[$key])) return $_SERVER[$key];
		if ($value = getenv($key) !== false) return $value;
		return $default;
	}

	public $root;

	function __construct($root)
	{
		$this->root = $root;
	}

	function _path()
	{
		return Gongo_Locator::get('Gongo_App_Path', $this->root);
	}

	function _configProduction()
	{
		return Gongo_Locator::get('Gongo_Config', $this->path->configProduction);
	}

	function _configDevelopment()
	{
		return Gongo_Locator::get('Gongo_Config', $this->path->configDevelopment);
	}

	function _configDefault()
	{
		return Gongo_Locator::get('Gongo_Config', $this->path->configFile);
	}

	function _devMode()
	{
		$devServer = $this->configDefault->Server->development;
		$prdServer = $this->configDefault->Server->production;
		$devMode = false;
		$serverAddr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '' ;
		$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '' ;
		if ($devServer !== '' && ($serverAddr === $devServer || $serverName === $devServer)) {
			$devMode = true;
		} else if ($prdServer !== '' && ($serverAddr === $prdServer || $serverName === $prdServer)) {
			$devMode = false;
		}
		return $devMode;
	}

	function _development()
	{
		return $this->devMode;
	}

	function _production()
	{
		return !$this->devMode;
	}

	function _config()
	{
		$base = $this->configDefault;
		$config = $this->devMode ? $this->configDevelopment : $this->configProduction ;
		return Gongo_Bean::mergeRecursive($base, $config);
	}

	function _autoloadPaths()
	{
		$paths = $this->config->Path->autoload;
		return Gongo_File_Path::preparePaths($paths, $this->path->root);
	}

	function _preloadPaths()
	{
		$paths = $this->config->Path->preload;
		return Gongo_File_Path::preparePaths($paths, $this->path->root);
	}

	function _log()
	{
		return Gongo_Locator::get('Gongo_Log', $this->path->logFile);
	}

	function _sqlLog()
	{
		return Gongo_Locator::get('Gongo_Log', $this->path->sqlLogFile);
	}

	function _pdoBuilder()
	{
		return Gongo_Locator::get('Gongo_App_DB_PDO_Builder');
	}

	function _pdo()
	{
		return $this->pdoBuilder->get($this);
	}

	function _useSqlLog()
	{
		return $this->config->Debug->use_sql_debug_log;
	}
}

