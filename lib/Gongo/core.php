<?php
/**********************************************************************
    Gongo Framework Core
    Written by Hiroyuki OHARA (c) copyright 2011-2013
    Gongo is dual Licensed MIT and GPLv3. You may choose the 
    license that fits best for your project.
**********************************************************************/

class Gongo_Container
{
	protected $factory = null;
	protected $components = array();
	public $uses = array();

	public function __construct($aComponents = array())
	{
		$this->initFactory($aComponents);
	}

	public function initFactory($aComponents = array())
	{
		$this->factory = Gongo_Locator::getInstance();
		$this->initializeComponents($aComponents);
	}
	
	public function __call($sName, $aArg)
	{
		if (strpos($sName, '_', 0) === 0) {
			return Gongo_Fn_Partial::apply(array($this, substr($sName, 1)), $aArg);
		}
	}

	public function __get($sName)
	{
		if (strpos($sName, '_', 0) === 0) {
			if (strpos($sName, '_', 1) !== 1) {
				return $this->factory->getObj('Gongo_Container_Promise', $this, substr($sName, 1));
			}
			$sName = substr($sName, 2);
			return isset($this->components[$sName]);
		} else if (isset($this->components[$sName])) {
			return $this->{$sName} = call_user_func($this->components[$sName]);
		}
		return null;
	}

	public function componentClasses($sClass = null)
	{
		$sClass = is_null($sClass) ? get_class($this) : $sClass ;
		$aVars = get_class_vars($sClass);
		$aComponents = isset($aVars['uses']) ? $aVars['uses'] : array() ;
		$sParent = get_parent_class($sClass);
		if (!$sParent) return $aComponents;
		$aParentComponents = $this->componentClasses($sParent);
		return array_merge($aParentComponents, $aComponents);
	}

	public function initializeComponents($aInjectComponents = null)
	{
		$aComponents = $this->componentClasses();
		if (!is_null($aInjectComponents)) {
			$aComponents = array_merge($aComponents, $aInjectComponents);
		}
		$aOptions = array();
		foreach ($aComponents as $sKey => $sClass) {
			if (strpos($sKey, '-', 0) === 0) {
				$aOptions[substr($sKey,1)] = $sClass;
			} else if (is_array($sClass)) {
				$args = $sClass;
				$sClass = array_shift($args);
				$sName = is_string($sKey) ? $sKey : $sClass ;
				$this->components[$sName] = Gongo_Fn::quote($this->factory)->makeObj($sClass, $args);
			} else if (!is_null($sClass)) {
				$sName = is_string($sKey) ? $sKey : $sClass ;
				$this->components[$sName] = Gongo_Fn::quote($this->factory)->getObj($sClass);
			}
		}
		$this->components['options'] = Gongo_Fn::quote($this->factory)->getObj('Gongo_Bean_ArrayWrapper', $aOptions);
	}

	public function attach($mName, $mClass = null)
	{
		if (is_array($mName)) {
			$sClass = array_shift($mName);
			if(property_exists($this, $sClass)) unset($this->{$sClass});
			$this->components[$sClass] = Gongo_Fn::quote($this->factory)->makeObj($sClass, $mName);
			return $this;
		}
		if (is_array($mClass)) {
			$sClass = array_shift($mClass);
		} else {
			$sClass = is_null($mClass) ? $mName : $mClass ;
		}
		if(property_exists($this, $mName)) unset($this->{$mName});
		if (is_array($mClass)) {
			$this->components[$mName] = Gongo_Fn::quote($this->factory)->makeObj($sClass,$mClass);
		} else {
			$this->components[$mName] = Gongo_Fn::quote($this->factory)->getObj($sClass);
		}
		return $this;
	}
	
	public function afterInit($sName, $callback)
	{
		if (isset($this->components[$sName])) {
			$this->components[$sName] = Gongo_Fn::after($this->components[$sName], $callback);
		}
	}

	public function register($sName, $callback)
	{
		$this->components[$sName] = $callback;
	}
	
	public function defaultValue($options, $sName, $mValue)
	{
		if (!isset($options[$sName])) {
			$options[$sName] = $mValue;
		}
		return $options;
	}
}

class Gongo_App_Base extends Gongo_Container
{
	static protected $cfg = null;

	static public function cfg($oCfg = null)
	{
		if (is_null($oCfg)) return self::$cfg;
		self::$cfg = $oCfg; 
	}

	public function __get($sName)
	{
		if (strpos($sName, '_', 0) === 0) {
			if (strpos($sName, '_', 1) !== 1) {
				return $this->factory->getObj('Gongo_Container_Promise', $this, substr($sName, 1));
			}
			$sName = substr($sName, 2);
			return isset($this->components[$sName]) || isset($this->components['/'.$sName]) ;
		} else if (isset($this->components[$sName])) {
			return $this->{$sName} = call_user_func($this->components[$sName]);
		} else if (isset($this->components['/'.$sName])) {
			return $this->{$sName} = call_user_func($this->components['/'.$sName]);
		}
		return null;
	}
}

class Gongo_App extends Gongo_App_Base
{
	static $appRootPath = null;
	static $environment = null;
	static $application = null;

	protected $filters = array(
		'before' => array(),
		'after' => array(),
		'around' => array(),
	);
	protected $mappings = array();
	public $uses = array(
		'session' => 'Gongo_App_Wrapper_Session',
		'cookie' => 'Gongo_App_Wrapper_Cookie',
		'request' => 'Gongo_App_Wrapper_Request',
		'post' => 'Gongo_App_Wrapper_Post',
		'get' => 'Gongo_App_Wrapper_Get',
		'files' => 'Gongo_App_Wrapper_Files',
		'file' => 'Gongo_App_File',
		'server' => 'Gongo_App_Wrapper_Server',
		'url' => 'Gongo_App_Url_Router',
		'dispatcher' => 'Gongo_App_Dispatcher',
		'config' => 'Gongo_Config',
		'template' => 'Gongo_App_Html_Template',
		'httpError' => 'Gongo_App_HttpError',
		'errorHandler' => 'Gongo_App_ErrorHandler',
		'exceptionHandler' => 'Gongo_App_ExceptionHandler',
		'errorTemplate' => 'Gongo_App_Html_Template',
		'context' => array('Gongo_Bean', array()),
		'success' => array('Gongo_Bean', array()),
		'error' => array('Gongo_Bean', array()),
		'root' => null,
		'basepath' => null,
	);

	static function autoload($className)
	{
		$ns = '';
		if (false !== ($lastNsPos = strrpos($className, '\\'))) {
			$ns = Gongo_File_Path::make(strtr(substr($className, $lastNsPos), array('\\' => '/')));
			$className = substr($className, $lastNsPos + 1);
		}
		$filePath = Gongo_File_Path::make('/' . strtr($className, array('_' => '/')) . '.php');
		$paths = self::$environment->autoloadPaths;
		foreach($paths as $path) {
			if (is_file($path . $ns . $filePath)) {
				require($path . $ns . $filePath);
				return;
			}
		}
	}

	static function preload()
	{
		$paths = self::$environment->preloadPaths;
		foreach($paths as $path) {
			if (is_file($path)) {
				include($path);
			}
		}
	}

	static function initializeApplication($app, $path = null)
	{
		if (!is_null($path) && is_null(self::$appRootPath)) {
			self::$appRootPath = $path;
			if (is_null(self::$environment)) {
				self::$environment = Gongo_App_Environment::get(self::$appRootPath);
			}
			if (is_null(self::cfg())) {
				self::cfg(self::$environment->config);
			}
			// Locator
			$locator = Gongo_Locator::getInstance();
			$locator->config(self::$environment->config);
			$gongoBuilderClass = self::cfg()->Locator->Gongo_Builder;
			if ($gongoBuilderClass) {
				$locator->injectBuilder($gongoBuilderClass);
				self::$environment = Gongo_App_Environment::get(self::$appRootPath, true);
				self::cfg(self::$environment->config);
			}
			$errorReporting = self::cfg()->Error->error_reporting;
			if ($errorReporting) error_reporting($errorReporting);
			// autoloader
			spl_autoload_register('Gongo_App::autoload');
			// preload
			self::preload();
		}
		if (is_null(self::$application)) {
			self::$application = $app;
		}
		return $app;
	}

	public function __construct($path = null, $options = array())
	{
		self::initializeApplication($this, $path);
		parent::__construct($options);
		$this->initializeSession();
		session_start();
		$this->initializeErrorHandler();
		$this->initializeExceptionHandler();
		$this->afterInit('url', $this->_afterInitUrl());
		self::cfg() && $this->afterInit('config', $this->_afterInitConfig());
	}

	public function afterInitUrl($obj)
	{
		return $obj->init($this);
	}

	public function afterInitConfig($obj)
	{
		return $obj->_(self::cfg()->_());
	}

	protected function initializeErrorHandler()
	{
		self::cfg() && self::cfg()->Error->use_error_handler(true) and set_error_handler(
			$this->_errorHandler->_handleError($this), error_reporting()
		);
	}

	protected function initializeExceptionHandler()
	{
		self::cfg() && self::cfg()->Error->use_exception_handler(true) and set_exception_handler(
			$this->_exceptionHandler->_handleException($this)
		);
	}

	protected function initializeSession()
	{
		ini_set('session.serialize_handler', 'php');
		$sessionName = self::cfg() ? self::cfg()->Session->session_name('gongossid') : 'gongossid' ;
		ini_set('session.name', $sessionName);
		$cookieLifetime = self::cfg() ? self::cfg()->Session->cookie_lifetime(0) : 0 ;
		ini_set('session.cookie_lifetime', $cookieLifetime);
		ini_set('session.auto_start', 0);
		$sessionSavePath = self::cfg() ? self::cfg()->Session->save_path : null ;
		if ($sessionSavePath) {
			ini_set('session.save_path', $this->env()->path->sessionSavePath($sessionSavePath));
		}
	}

	public function error($err, $fnCallback = false)
	{
		$this->httpError->render($this, $err, $fnCallback);
	}

	public function http($method, $url, $callback, $conditions=array())
	{
		$this->event($method, $url, $callback, $conditions, $this->mappings);
	}

	public function get($url, $callback, $conditions=array())
	{
		$this->event('get', $url, $callback, $conditions, $this->mappings);
	}

	public function post($url, $callback, $conditions=array())
	{
		$this->event('post', $url, $callback, $conditions, $this->mappings);
	}

	public function put($url, $callback, $conditions=array())
	{
		$this->event('put', $url, $callback, $conditions, $this->mappings);
	}

	public function delete($url, $callback, $conditions=array())
	{
		$this->event('delete', $url, $callback, $conditions, $this->mappings);
	}

	protected function event($httpMethod, $url, $callback, $conditions=array(), &$mappings)
	{
		if (is_string($callback)) {
			array_push($mappings, array($httpMethod, $url, $callback, $conditions));
		} else if (is_array($callback)) {
			array_push($mappings, array($httpMethod, $url, $callback, $conditions));
		} else if (is_object($callback)) {
			array_push($mappings, array($httpMethod, $url, $callback, $conditions));
		}
	}

	public function init($path = null)
	{
		$path = is_null($path) ? $this->basepath() : $path ;
		$path = is_null($path) ? '' : $path ;
		if (is_null($this->root)) {
			$this->basepath($path);
			$this->url->initRoute($this, $this->uses, $path, array());
			if (self::cfg()->Dispatcher->use_dispatcher(true)) {
				$this->dispatcher->initContoroller($this, $this, '', array());
			}
		} else {
			$this->root->init($this, $path, array(), $this);
		}
		return $this;
	}

	public function run()
	{
		echo $this->processRequest();
	}

	protected function processRequest()
	{
		$url = $this->url;
		foreach ($this->mappings as $mapping) {
			if ($url->match($mapping[0], $mapping[1], $mapping[3])) {
				return $this->execute($mapping[2], $url, $mapping[0]);
			}
		}
		return $this->error('404');
	}

	public function setSessionValue($args)
	{
		if ($this->session->error) {
			$this->error = $this->session->error;
			$this->session->error = null;
		}
		if ($this->session->success) {
			$this->success = $this->session->success;
			$this->session->success = null;
		}
	}

	protected function setFilterCallback($kind, $callback, $url)
	{
		if (!isset($this->filters[$kind])) return $callback;
		$filters = $kind !== 'after' ? array_reverse($this->filters[$kind]) : $this->filters[$kind] ;
		foreach ($filters as $filter) {
			if ($url->match($filter[0], $filter[1], $filter[3])) {
				$callback = Gongo_Fn::$kind($callback, $filter[2]);
			}
		}
		return $callback;
	}

	protected function execute($callback, $url, $method)
	{
		$method = strtoupper($method);
		$params = $url->params;
		$callback = Gongo_Fn::before($callback, Gongo_Fn::quote($this)->setSessionValue);
		$urlMatch = $this->factory->getObj('Gongo_App_Url_Router', array('-mountPoint' => $url->options->mountPoint));
		$this->afterInitUrl($urlMatch);
		foreach (array('around','before','after') as $kind) {
			$callback = $this->setFilterCallback($kind, $callback, $urlMatch);
		}
		$this->attach('args', array('Gongo_Bean_ArrayWrapper', $params));
		return call_user_func_array($callback, $params);
	}

	public function before($httpMethod, $urls, $callback, $conditions=array())
	{
		$this->event($httpMethod, $urls, $callback, $conditions, $this->filters['before']);
	}
	public function after($httpMethod, $urls, $callback, $conditions=array())
	{
		$this->event($httpMethod, $urls, $callback, $conditions, $this->filters['after']);
	}

	public function around($httpMethod, $urls, $callback, $conditions=array())
	{
		$this->event($httpMethod, $urls, $callback, $conditions, $this->filters['around']);
	}

	public function redirect($path, $isFull = false, $exit = true, $type = 0)
	{
		$path = $this->replacePathArgs($path);
		$uri = $isFull ? $path : $this->url->path($path, false, $type);
		$this->session->error = $this->error;
		$this->session->success = $this->success;
		header("Location: {$uri}");
		$exit && exit();
	}

	public function render($viewName, $context = array(), $template = null)
	{
		$context = array_merge($this->context->_(), $context);
		$context['app'] = $this;
		$context['viewName'] = $viewName;
		$context['mountPoint'] = $this->url->options->mountPoint;
		$context['error'] = $this->error;
		$context['success'] = $this->success;
		if (!isset($context['layout'])) {
			$context['layout'] = Gongo_App::cfg()->Template->layout;
		}
		if (is_null($template)) {
			return $this->template->render($context, $viewName);
		}
		return $template->render($context, $viewName);
	}

	public function sendFile($path, $filename = null, $contentType = null, $type = 'inline', $nosniff = true)
	{
		return $this->file->send($path, $filename, $contentType, $type);
	}

	public function sendDownload($path, $filename = null, $nosniff = true)
	{
		return $this->file->download($filename, $path);
	}

	public function replacePathArgs($path, $args = array(), $hash = null, $short = true)
	{
		return $this->url->replacePathArgs($path, $this->args->_(), $args, $hash, $short);
	}

	public function env()
	{
		return self::$environment;
	}

	public function log($text)
	{
		return $this->env()->log->add($text);
	}

	public function basepath($path = null)
	{
		if (is_null($path)) return $this->basepath;
		$this->basepath = $path;
		return $this;
	}

	public function path($query = null, $args = null, $action = null, $basepath = null, $type = 0, $short = false)
	{
		return $this->dispatcher->path($this, $query, $args, $action, $basepath, $type, $short);
	}
}

class Gongo_Component_ContainerException extends Exception {}

class Gongo_Component_Container
{
	protected $components = array();
	protected $callbacks = array();
	
	function __get($name)
	{
		if (array_key_exists($name, $this->components)) {
			return $this->components[$name];
		}
		if (array_key_exists($name, $this->callbacks)) {
			$this->components[$name] = call_user_func($this->callbacks[$name]);
			return $this->components[$name];
		}
		$method = '_' . $name;
		if (method_exists($this, $method)) {
			$this->components[$name] = $this->{$method}();
			return $this->components[$name];
		}
		throw new Gongo_Component_ContainerException('method not found: '. get_class($this) . '::' . $method);
	}

	function __call($name, $args)
	{
		if (array_key_exists($name, $this->components)) {
			return $this->components[$name];
		}
		$method = '_' . $name;
		if (!method_exists($this, $method)) {
			throw new Gongo_Component_ContainerException('method not found: '. get_class($this) . '::' . $method);
		}
		$this->components[$name] = call_user_func_array(array($this, $method), $args);
		return $this->components[$name];
	}
	
	function _($name, $callback)
	{
		$this->callbacks[$name] = $callback;
		return $this;
	}
}

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


class Gongo_Locator
{
	static public $defaultConfig = null;
	static $serviceLocator = null;
	static $defaultBuilder = 'Gongo_Builder';
	static $environmentVariable = 'GONGO_BUILDER';
	protected $serviceBuilder = null;
	protected $config = null;
	protected $refParams = array();
	protected $refClasses = array();

	static function setConfig($cfg = null)
	{
		self::$defaultConfig = is_null($cfg) ? Gongo_Config::get() : $cfg ;
	}

	static public function getInstance()
	{
		if (is_null(self::$serviceLocator)) {
			self::$serviceLocator = new self;
		}
		return self::$serviceLocator;
	}

	static public function get()
	{
		$serviceLocator = self::getInstance();
		$args = func_get_args();
		$className = array_shift($args);
		return self::build($serviceLocator, $className, $args);
	}

	static public function make($className, $args)
	{
		$serviceLocator = self::getInstance();
		return self::build($serviceLocator, $className, $args);
	}

	static public function _make($className, $args)
	{
		return Gongo_Fn::papply('Gongo_Locator::make', $className, $args);
	}

	static public function _get()
	{
		$args = func_get_args();
		$className = array_shift($args);
		return self::_make($className, $args);
	}

	static public function makeLazy($className, $args, $after = null, $singleton = false)
	{
		$callback = Gongo_Fn::papply('Gongo_Locator::make', $className, $args);
		if (!is_null($after)) $callback = Gongo_Fn::after($callback, $after);
		if ($singleton) $callback = Gongo_Fn::once($callback);
		return self::get('Gongo_Proxy_Lazy', $callback);
	}

	static public function getLazy()
	{
		$args = func_get_args();
		$className = array_shift($args);
		return self::makeLazy($className, $args);
	}

	static public function build($serviceLocator, $className, $args)
	{
		$serviceBuilder = $serviceLocator->builder();
		$method = 'build_' . $className;
		if (!is_null($serviceBuilder) && method_exists($serviceBuilder, $method)) {
			$obj = call_user_func_array(array($serviceBuilder, $method), $args);
			if (!is_null($obj)) return $obj;
		}
		$config = $serviceLocator->config();
		if (!is_null($config)) {
			$className = $config->Locator->{$className} ? $config->Locator->{$className} : $className ;
		}
		return $serviceLocator->newObj($className, $args);
	}

	static function load($path, $className = null, $autoload = false)
	{
		if (!is_null($className) && class_exists($className, $autoload)) return;
		include($path);
	}

	public function builder($builder = null)
	{
		if (is_null($builder)) return $this->serviceBuilder;
		$this->serviceBuilder = $builder;
		return $this;
	}

	public function injectBuilder($builderClass, $args = array())
	{
		$this->builder($this->newObj($builderClass, $args));
	}

	public function config($config = null)
	{
		if (is_null($config)) return $this->config;
		$this->config = $config;
		return $this;
	}

	public function __construct($builder = null)
	{
		if (!is_null(self::$defaultConfig)) {
			$this->config(self::$defaultConfig);
		}
		if (is_null($builder)) {
			$environmentVariable = self::$environmentVariable;
			$builderClass = isset($_SERVER[$environmentVariable]) ? $_SERVER[$environmentVariable] : false ;
			if (!$builderClass) $builderClass = getenv($environmentVariable);
			if (!$builderClass) $builderClass = self::$defaultBuilder;
			$config = $this->config();
			if (!is_null($config)) {
				$builderClass = $config->Locator->Gongo_Builder ? $config->Locator->Gongo_Builder : $builderClass ;
			}
			$builder = $this->newObj($builderClass);
		}
		$this->builder($builder);
	}

	public function getObj()
	{
		$serviceBuilder = $this->builder();
		$args = func_get_args();
		$className = array_shift($args);
		return self::build($this, $className, $args);
	}

	public function makeObj($className, $args)
	{
		return self::build($this, $className, $args);
	}

	protected function newObj($sClass, $args = array())
	{
		if (!$sClass) return null;
		if (count($args) === 0) return new $sClass;
		if (isset($this->refParams[$sClass])) {
			$params = $this->refParams[$sClass];
		} else {
			$refMethod = new ReflectionMethod($sClass,  '__construct');
			$params = $this->refParams[$sClass] = $refMethod->getParameters();
		}
		$re_args = array();
		foreach($params as $key => $param) {
			if (isset($args[$key])) {
				if ($param->isPassedByReference()) {
					$re_args[$key] = &$args[$key];
				} else {
					$re_args[$key] = $args[$key];
				}
			}
		}
		$refClass = isset($this->refClasses[$sClass]) ? $this->refClasses[$sClass] : $this->refClasses[$sClass] = new ReflectionClass($sClass) ;
		return $refClass->newInstanceArgs((array) $re_args);
	}
}

class Gongo_Builder 
{
}

class Gongo_App_Path extends Gongo_Component_Container
{
	public $root;

	function __construct($root)
	{
		$this->root = $root;
	}

	function _app()
	{
		return $this->root . Gongo_File_Path::make('/app');
	}

	function _temp()
	{
		$path = $this->root . Gongo_File_Path::make('/work');
		Gongo_File::makeDir($path);
		return $path;
	}

	function _sessionSavePath($path)
	{
		return $this->root . Gongo_File_Path::make($path);
	}

	function _template()
	{
		return $this->root . Gongo_File_Path::make('/template');
	}

	function _config()
	{
		return $this->root . Gongo_File_Path::make('/config');
	}

	function _configFile()
	{
		return $this->config . Gongo_File_Path::make('/config.ini');
	}

	function _configDevelopment()
	{
		return $this->config . Gongo_File_Path::make('/development.ini');
	}

	function _configProduction()
	{
		return $this->config . Gongo_File_Path::make('/production.ini');
	}

	function _log()
	{
		if (!$this->temp) return false;
		$path = $this->temp . Gongo_File_Path::make('/logs');
		Gongo_File::makeDir($path);
		return $path;
	}

	function _logFile()
	{
		if (!$this->log) return false;
		return $this->log . Gongo_File_Path::make('/log_' . date('Y-m-d') . '.txt');
	}

	function _sqlLogFile()
	{
		if (!$this->log) return false;
		return $this->log . Gongo_File_Path::make('/sqllog_' . date('Y-m-d') . '.txt');
	}

	function _webapp()
	{
		return dirname(dirname($this->root));
	}

	function _lib()
	{
		return $this->webapp . Gongo_File_Path::make('/lib');
	}

	function _home()
	{
		return dirname($this->webapp);
	}

	function _htmlPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_DocumentRoot', $this);
	}

	function _html()
	{
		return $this->htmlPath->html;
	}

	function _css()
	{
		return $this->htmlPath->css;
	}

	function _js()
	{
		return $this->htmlPath->js;
	}

	function _img()
	{
		return $this->htmlPath->img;
	}

	function _domain()
	{
		return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '' ;
	}

	function _port()
	{
		return isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '' ;
	}

	function _https()
	{
		return isset($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN);
	}


	function _scheme()
	{
		return 'http' . ($this->https ? 's' : '') ;
	}

	function _rootUrl()
	{
		$scheme = $this->scheme;
		$httpHost = $this->domain;
		$port = $this->port;
		if ($scheme === 'https' && $port === ':443') $port = '';
		return $scheme . '://' . $httpHost . $port ;
	}

	function _rootUrlHttp()
	{
		$httpHost = $this->domain;
		$port = $this->port;
		if ($port === ':443') $port = '';
		return 'http://' . $httpHost . $port ;
	}

	function _rootUrlHttps()
	{
		$httpHost = $this->domain;
		$port = $this->port;
		if ($port === ':443') $port = '';
		return 'https://' . $httpHost . $port ;
	}

	function _originalRequestPath()
	{
		return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	}

	function _requestPath()
	{
		if (isset($_SERVER['GONGO_MOUNT_POINT'])) {
			$mountPoint = $_SERVER['GONGO_MOUNT_POINT'];
			$path = substr($this->originalRequestPath, strlen($mountPoint));
			if ($path === '/') $path = '';
		} else {
			$path = isset($_GET['__url__']) ? $_GET['__url__'] : '' ;
			if ($path != '' && !Gongo_Str::startsWith($path, '/')) $path = '/' . $path;
		}
		return $path;
	}

	function _mountPoint()
	{
		$reqUrl = $this->originalRequestPath;
		if ($reqUrl == '/index.php' && $this->requestPath == '') {
			$this->requestPath = 'index.php' ;
			$mountPoint = '';
		} else if (Gongo_Str::endsWith($reqUrl, '/') &&  $this->requestPath == '') {
			$mountPoint = substr(urldecode($reqUrl), 0, -1) ;
		} else {
			$mountPoint = substr(urldecode($reqUrl), 0, -strlen($this->requestPath));
		}
		return $mountPoint === '/' ? '' : $mountPoint ;
	}

	function _requestUrl()
	{
		return Gongo_Str::startsWith($this->requestPath, '/') ? $this->requestPath : '/' . $this->requestPath ;
	}

	function _smarty2()
	{
		return Gongo_Locator::get('Gongo_App_Path_Smarty2', $this);
	}

	function _smarty3()
	{
		return Gongo_Locator::get('Gongo_App_Path_Smarty3', $this);
	}

	function _php()
	{
		return Gongo_Locator::get('Gongo_App_Path_Template', $this, 'php');
	}

	function _twig()
	{
		return Gongo_Locator::get('Gongo_App_Path_Twig', $this);
	}
}

class Gongo_File_Path
{
	static function make($path)
	{
		$aPath = explode('/', $path);
		return implode(DIRECTORY_SEPARATOR, $aPath);
	}

	static function preparePaths($paths, $root = '')
	{
		$paths = !is_array($paths) ? explode(',', $paths) : $paths ;
		$paths = array_filter(array_map('trim', $paths));
		$absPaths = array();
		foreach ($paths as $path) {
			$rpath = self::make($root . '/' . $path);
			$apath = realpath($rpath);
			
			$absPaths[] = $apath ? $apath : $rpath ;
		}
		return $absPaths;
	}

	static function absolutePath($path, $root = null)
	{
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
		$absolutes = array();
		foreach ($parts as $part) {
			if ('.' == $part) continue;
			if ('..' == $part) {
				array_pop($absolutes);
			} else {
				$absolutes[] = $part;
			}
		}
		return (is_null($root) ? DIRECTORY_SEPARATOR : $root) . implode(DIRECTORY_SEPARATOR, $absolutes);
	}

	static function basename($path) 
	{
		$pos = strrpos($path, DIRECTORY_SEPARATOR);
		return $pos === false ? $path : substr($path, $pos + 1);
	}
}

class Gongo_Bean_Base 
{
	public $__ = null;
	
	public function __get($key)
	{
		if ($key === '_') {
			if (is_null($this->__)) return null;
			$aComponents = $this->___initializeComponents();
			$this->_ = Gongo_Locator::get('Gongo_Container', $aComponents);
			return $this->_;
		}
		return null;
	}

	public function ___componentClasses($sClass = null)
	{
		$sClass = is_null($sClass) ? get_class($this) : $sClass ;
		$aVars = get_class_vars($sClass);
		$aComponents = isset($aVars['__']) ? $aVars['__'] : array() ;
		$sParent = get_parent_class($sClass);
		if (!$sParent) return $aComponents;
		$aParentComponents = $this->___componentClasses($sParent);
		return array_merge($aParentComponents, $aComponents);
	}

	public function ___initializeComponents($aInjectComponents = null)
	{
		$aComponents = $this->___componentClasses();
		if (!is_null($aInjectComponents)) {
			$aComponents = array_merge($aComponents, $aInjectComponents);
		}
		return $aComponents;
	}
}

class Gongo_Bean extends Gongo_Bean_Base implements IteratorAggregate
{
	protected $_data;
	
	static function import($bean, $data, $attr = null)
	{
		$ary = array();
		foreach ($data as $k => $v) {
			if (is_null($attr) || in_array($k, $attr)) {
				$ary[$k] = is_array($v) ? Gongo_Locator::get('Gongo_Bean', $v) : $v ;
			}
		}
		return $bean->_($ary);
	}

	static function export($bean)
	{
		$ary = array();
		foreach ($bean->_() as $k => $v) {
			$ary[$k] = $v instanceof Gongo_Bean ? self::export($v) : $v ;
		}
		return $ary;
	}
	
	static function merge($bean1, $bean2, $attr = null)
	{
		$data = $bean2 instanceof Gongo_Bean ? $bean2->_() : (array) $bean2 ;
		foreach ($data as $k => $v) {
			if (is_null($attr) || in_array($k, $attr)) {
				$bean1->{$k} = $v;
			}
		}
		return $bean1;
	}
	
	static function mergeRecursive($bean1, $bean2, $attr = null)
	{
		$data = $bean2 instanceof Gongo_Bean ? $bean2->_() : (array) $bean2 ;
		foreach ($data as $k => $v) {
			if (is_null($attr) || in_array($k, $attr)) {
				if ($v instanceof Gongo_Bean) {
					$bean = $bean1->{$k} instanceof Gongo_Bean ? $bean1->{$k} : Gongo_Locator::get('Gongo_Bean') ;
					$bean1->{$k} = self::mergeRecursive($bean, $v);
				} else {
					$bean1->{$k} = $v;
				}
			}
		}
		return $bean1;
	}

	static function cast($bean, $data, $strict = true, $unset = false)
	{
		$beanData = $bean->_();
		if (empty($beanData)) return self::merge($bean, $data);
		$srcData = $data instanceof Gongo_Bean ? $data->_() : (array) $data ;
		foreach ($srcData as $k => $v) {
			if (array_key_exists($k, $beanData)) {
				$type = $beanData[$k];
				if (is_int($type)) {
					$bean->{$k} = (int) $v;
				} else if (is_float($type)) {
					$bean->{$k} = (float) $v;
				} else if (is_string($type)) {
					$bean->{$k} = (string) $v;
				} else if (is_bool($type)) {
					$bean->{$k} = (bool) $v;
				} else {
					$bean->{$k} = $v;
				}
			} else if (!$strict) {
				$bean->{$k} = $v;
			}
		}
		if ($unset) {
			$beanKey = array_keys($beanData);
			$srcKey = array_keys($srcData);
			foreach (array_diff($beanKey, $srcKey) as $k) {
				unset($bean->{$k});
			}
		}
		return $bean;
	}

	function __construct($ary = array())
	{
		self::import($this, $ary);
	}
	
	public function __get($key)
	{
		if ($key === '_') return parent::__get($key);
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
		return $value;
	}
	
	public function __isset($key)
	{
		return isset($this->_data[$key]);
	}
	
	public function __unset($key)
	{
		unset($this->_data[$key]);
	}
	
	public function __call($name, $args)
	{
		$default = array_shift($args);
		$value = $this->{$name};
		return is_null($value) ? $default : $value ;
	}
	
	public function _($ary = null)
	{
		if (is_null($ary)) {
			return $this->_data;
		}
		$this->_data = $ary;
		return $this;
	}

	public function __()
	{
		return $this->_(array());
	}
	
	public function ___($ary = array())
	{
		$className = get_class($this);
		return Gongo_Locator::get($className, $ary);
	}
	
	public function getIterator() 
	{
		return Sloth::iter(new ArrayIterator($this->_data));
	}
}

class Gongo_Config extends Gongo_Bean
{
	static $config = null;
	
	static function get($cfg = null)
	{
		if (is_null(self::$config)) {
			$cfg = is_null($cfg) ? dirname(__FILE__) . '/config.ini' : $cfg ;
			self::$config = Gongo_Locator::get('Gongo_Config', $cfg);
		}
		return self::$config;
	}
	
	function __construct($cfg = array())
	{
		$cfg = is_string($cfg) ? parse_ini_file($cfg, true) : $cfg ;
		parent::__construct($cfg);
	}
}

class Gongo_Fn
{
	static function quote($obj)
	{
		return Gongo_Locator::get('Gongo_Fn_Quotation', $obj);
	}

	static function callee()
	{
		list(, $frame) = debug_backtrace() + array(1 => false);
		if (!$frame) throw new BadFunctionCallException('You must call in function');
		$callback = isset($frame['object']) ? array($frame['object'], $frame['function']) :
			(isset($frame['class']) ? array($frame['class'], $frame['function']) :
			$frame['function']);
		$args = func_get_args();
		return $args ? Gongo_Fn_Partial::apply($callback, $args) : $callback;
	}

	static function method($name)
	{
		list(, $frame) = debug_backtrace() + array(1 => false);
		if (!isset($frame['class'])) throw new BadFunctionCallException('You must call in class method');
		$callback = array(isset($frame['object']) ? $frame['object'] : $frame['class'], $name);
		$args = func_get_args();
		array_shift($args);
		return $args ? Gongo_Fn_Partial::apply($callback, $args) : $callback;
	}

	static function papply($callback)
	{
		$args = func_get_args();
		array_shift($args);
		return Gongo_Fn_Partial::apply($callback, $args);
	}

	static function call($callback)
	{
		$args = func_get_args();
		array_shift($args);
		return call_user_func_array($callback, $args);
	}

	static function apply($callback, $args)
	{
		return call_user_func_array($callback, $args);
	}

	static function once($callback)
	{
		return Gongo_Locator::get('Gongo_Fn_Once', $callback);
	}

	static function create($callback, $args = array())
	{
		return new Gongo_Fn_Partial($callback, $args);
	}
	
	static function before($callback, $before)
	{
		return Gongo_Fn::create($callback)->before($before)->fetch();
	}

	static function after($callback, $after)
	{
		return Gongo_Fn::create($callback)->after($after)->fetch();
	}

	static function around($callback, $around)
	{
		return Gongo_Fn::create($callback)->around($around)->fetch();
	}
	
	static function reflection($callback)
	{
		if (is_string($callback)) {
			if (strpos($callback, '::') === false) {
				// function
				return new ReflectionFunction($callback);
			} else {
				// static method
				return new ReflectionMethod($callback);
			}
		} else if (is_array($callback)) {
			$func = $callback[0];
			if (is_string($func)) {
				// function or static method
				return self::reflection($func);
			} else if (is_object($func)) {
				if ($func instanceof Gongo_Fn_Partial) {
					// partial application object
					return $func;
				} else {
					// instance method
					return new ReflectionMethod(get_class($func), $callback[1]);
				}
			}
		}
		return null;
	}
	
	static function params($callback)
	{
		$ref = self::reflection($callback);
		return $ref ? $ref->getParameters() : null ;
	}

	static function arity($callback)
	{
		$ref = self::reflection($callback);
		return $ref ? $ref->getNumberOfParameters() : null ;
	}
	
	static function curry($callback)
	{
		return Gongo_Fn_Curry::make($callback);
	}

	static function property($obj)
	{
		return Gongo_Locator::get('Gongo_Fn_Property', $obj);
	}
}


class Gongo_Fn_Quotation
{
	protected $___;
	
	function __construct($obj)
	{
		$this->___ = $obj;
	}
	function __get($name)
	{
		return array($this->___, $name);
	}
	function __call($name, $args)
	{
		return Gongo_Fn_Partial::apply($this->{$name}, $args);
	}
}

class Gongo_Fn_Partial
{
	protected $callback;
	protected $arguments;
	protected $before;
	protected $after;
	protected $around;
	
	public function __construct($callback, Array $args = array())
	{
		if (!is_callable($callback)) throw new InvalidArgumentException('$callback must be callable');
		$this->callback = $callback;
		$this->arguments = $args;
	}
	
	static function apply($callback, $args = null)
	{
		return $args ? array(new self($callback, $args), 'invoke') : $callback;
	}
	
	function before($before = null)
	{
		$this->before = $before;
		return $this;
	}

	function after($after = null)
	{
		$this->after = $after;
		return $this;
	}

	function around($around = null)
	{
		$this->around = $around;
		return $this;
	}
	
	function fetch($args = array())
	{
		if (!empty($args)) {
			$this->arguments = array_merge($this->arguments, $args);
		}
		return array($this, 'invoke');
	}
	
	function invoke()
	{
		$args = func_get_args();
		if ($this->before) {
			$returnArgs = call_user_func($this->before, $args);
			if (!is_array($returnArgs) && !is_null($returnArgs)) {
				return $returnArgs;
			}
			$args = !is_null($returnArgs) ? $returnArgs : $args ;
		}
		$args = array_merge($this->arguments, $args);
		if ($this->around) {
			$returnValue = call_user_func_array($this->around, array($this->callback, $args));
		} else {
			$returnValue = call_user_func_array($this->callback, $args);
		}
		if ($this->after) {
			return call_user_func($this->after, $returnValue);
		}
		return $returnValue;
	}
	/**
	 * __invoke
	 * for PHP5.3
	 */
	public function __invoke()
	{
		return call_user_func_array(array($this, 'invoke'), func_get_args());
	}

	public function getParameters()
	{
		$params = Gongo_Fn::params($this->callback);
		return !is_null($params) ? array_slice($params, count($this->arguments)) : null ;
	}

	public function getNumberOfParameters()
	{
		$arity = Gongo_Fn::arity($this->callback);
		return !is_null($arity) ? $arity - count($this->arguments) : null ;
	}
}
class Gongo_Container_Promise
{
	public $__obj;
	public $__name;
	
	public function __construct($obj, $name) 
	{
		$this->__obj = $obj; 
		$this->__name = $name; 
	}

	public function __get($sName) 
	{
		if (strpos($sName, '_', 0) === 0) {
			return new self($this, substr($sName, 1));
		}
		return $this->__obj->{$this->__name}->{$sName};
	}
	
	public function __force()
	{
		$aArgs = func_get_args();
		$sName = array_shift($aArgs);
		$aBind = array_shift($aArgs);
		$aArgs = array_merge($aBind, $aArgs);
		return call_user_func_array(array($this->__obj->{$this->__name}, $sName), $aArgs);
	}
	
	public function __call($sName, $aArg)
	{
		if (strpos($sName, '_', 0) === 0) {
			return Gongo_Fn_Partial::apply(array($this, '__force'), array(substr($sName, 1), $aArg));
		}
		return call_user_func_array(array($this->__obj->{$this->__name}, $sName), $aArg);
	}
}

class Gongo_App_Controller_Base extends Gongo_App_Base
{
	public $uses = array(
		'dispatcher' => null,
		'view' => null,
		'-basepath' => null,
		'-conditions' => null,
		'-view' => null,
	);

	function initBeforeAction($app, $method, $dispatcher, $callback, $actions, $flag = true) 
	{
		if ($flag && $actions) {
			$dispatcher->beforeAction($app, $method, $callback,
				$this->options->basepath, $this->options->conditions, $actions
			);
		}
	}

	function initAfterAction($app, $method, $dispatcher, $callback, $actions, $flag = true) 
	{
		if ($flag && $actions) {
			$dispatcher->afterAction($app, $method, $callback,
				$this->options->basepath, $this->options->conditions, $actions
			);
		}
	}

	function initBeforeActions($app, $dispatcher, $path, $conditions, $parent) 
	{
	}

	function initAfterActions($app, $dispatcher, $path, $conditions, $parent) 
	{
	}

	function initRoute($app, $dispatcher, $path, $conditions, $parent)
	{
		$app->url->initRoute($app, $this->uses, $path, $conditions, $this);
		if ($app->config->Dispatcher->use_dispatcher(true)) {
			$dispatcher->initContoroller($app, $this, $path, $conditions);
		}
	}
	
	function init($app, $path = '', $conditions = array(), $parent = null)
	{
		$this->options->basepath = $path;
		$this->options->conditions = $conditions;
		$this->options->view = substr($path, 1);
		if (!$this->__view) $this->view = $parent->view;
		$dispatcher = $this->dispatcher ? $this->dispatcher : $app->dispatcher ;
		$app->before('*', "{$path}/.*", $this->_beforeFilter($app));
		$this->initBeforeActions($app, $dispatcher, $path, $conditions, $parent);
		$this->initAfterActions($app, $dispatcher, $path, $conditions, $parent);
		$this->initRoute($app, $dispatcher, $path, $conditions, $parent);
		return $this;
	}

	function beforeFilter($app)
	{
	}

	function basepath()
	{
		return $this->options->basepath;
	}

	function view($app)
	{
		return $this->view ? $this->view : $app->view ;
	}

	function render($app, $template, $context = array())
	{
		$view = $this->view($app);
		return $view->render($app, $this->options->view . $template, $context);
	}

	function redirect($app, $path)
	{
		$app->redirect($this->basepath() . $path);
	}
}

class Gongo_App_Controller extends Gongo_App_Controller_Base
{
	public $uses = array(
		'ajax' => null,
		'token' => null,
		'filter' => null,
		'converter' => null,
		'validator' => null,
		'-ajaxAction' => null,
		'-secureAction' => null,
		'-filterAction' => null,
		'-validateAction' => null,
		'-id' => '',
	);

	function initAjaxAction($app, $dispatcher, $method)
	{
		$this->initBeforeAction($app, $method, $dispatcher,
			$this->_ajax->_setExtensions($app, $dispatcher),
			$this->options->ajaxAction, $this->__ajax
		);
	}
	
	function initTokenAction($app, $dispatcher, $method)
	{
		$this->initBeforeAction($app, $method, $dispatcher,
			$this->_token->_isValidPost($app, $this->_tokenError($app)),
			$this->options->secureAction, $this->__token
		);
	}
	
	function initFilterAction($app, $dispatcher, $method)
	{
		$this->initBeforeAction($app, $method, $dispatcher,
			$this->_filter->_inputFilter($app, $app->post),
			$this->options->filterAction, $this->__filter
		);
	}

	function initConverterAction($app, $dispatcher, $method)
	{
		$this->initBeforeAction($app, $method, $dispatcher,
			$this->_converter->_inputFilter($app, $app->post),
			$this->options->filterAction, $this->__converter
		);
	}
	
	function initValidatorAction($app, $dispatcher, $method)
	{
		$this->initBeforeAction($app, $method, $dispatcher,
			$this->_validator->_execute($app, null, null),
			$this->options->validateAction, $this->__validator
		);
	}
	
	function initBeforeActions($app, $dispatcher, $path, $conditions, $parent) 
	{
		parent::initBeforeActions($app, $dispatcher, $path, $conditions, $parent);
		$this->initTokenAction($app, $dispatcher, 'POST');
		$this->initFilterAction($app, $dispatcher, 'POST');
		$this->initConverterAction($app, $dispatcher, 'POST');
		$this->initValidatorAction($app, $dispatcher, 'POST');
		$this->initAjaxAction($app, $dispatcher, '*');
	}

	function beforeFilter($app)
	{
		$view = $this->view($app);
		if ($this->__token) {
			$view->context->token = $this->token->token();
		}
		$view->context->controllerId = $this->options->id;
	}

	function tokenError($app)
	{
		return $app->error('403');
	}
}

class Gongo_Bean_ArrayWrapper extends Gongo_Bean
{
	public function __construct(&$data = array())
	{
		$this->_data = &$data;
	}

	public function &_($ary = null)
	{
		return $this->_data;
	}
}

class Gongo_App_Dispatcher extends Gongo_App_Base
{
	public $moduleName = null;
	public $controllerName = null;
	public $actionName = null;
	public $submitName = null;
	public $controller = null;
	public $controllerPath = null;
	public $methodName = null;
	public $https = null;
	public $buttonName = null;
	public $currentAction = null;
	public $currentExtension = null;
	public $currentArgs = null;
	public $currentArgMaps = array();
	
	public $uses = array(
		'converter' => 'Gongo_Str_CaseConvert',
		'-submitRegex' => '/^-(\w+)-$/',
		'-controllerClass' => ':module_Controller_:controller:secure',
		'-secureClass' => 'Secure',
		'-secureMethod' => 'Secure',
		'-indexAction' => 'index',
		'-argSeparator' => '/',
		'-useExtension' => true,
		'-extension' => 'html',
	);
	
	public function actionName()
	{
		return $this->actionName;
	}
	
	public function methodName()
	{
		return $this->methodName;
	}

	public function buttonName()
	{
		return $this->buttonName;
	}

	public function https()
	{
		return $this->https;
	}

	public function currentAction()
	{
		return $this->currentAction;
	}

	public function currentExtension($default = null)
	{
		return $this->currentExtension ? $this->currentExtension : $default ;
	}

	public function currentArgs()
	{
		return $this->currentArgs;
	}

	public function currentArgMaps()
	{
		return $this->currentArgMaps;
	}

	public function basepath()
	{
		return $this->controller->basepath();
	}

	public function allowedExtensions($extensions)
	{
		$extensions = is_array($extensions) ? implode('|', $extensions) : $extensions ;
		$this->options->extension = $extensions;
	}

	public function initContoroller($app, $controller, $basepath = '', $conditions = array())
	{
		$app->http('*', $basepath . '/:__action__:__args__', 
			$this->_executeController($app, $controller, array()),
			$conditions + array('__action__' => '\w+', '__args__' => '[^?]*')
		);
		$app->http('*', $basepath . '/', 
			$this->_executeIndexController($app, $controller, array()),
			$conditions
		);
		return $this;
	}

	public function beforeAction($app, $method, $callback, $path = '', $conditions = array(), $action = '\w+')
	{
		$app->before($method, "{$path}/:__action__:__args__", 
			$callback,
			$conditions + array('__action__' => $action, '__args__' => '[^?]*')
		);
		return $this;
	}
	
	public function afterAction($app, $method, $callback, $path = '', $conditions = array(), $action = '\w+')
	{
		$app->after($method, "{$path}/:__action__:__args__", 
			$callback,
			$conditions + array('__action__' => $action, '__args__' => '[^?]*')
		);
		return $this;
	}

	public function aroundAction($app, $method, $callback, $path = '', $conditions = array(), $action = '\w+')
	{
		$app->around($method, "{$path}/:__action__:__args__", 
			$callback,
			$conditions + array('__action__' => $action, '__args__' => '[^?]*')
		);
		return $this;
	}
	
	public function makeControllerClassName($module, $controller, $secure)
	{
		$this->moduleName = Gongo_Str::snakeToPascal($module);
		$this->controllerName = Gongo_Str::snakeToPascal($controller);
		$this->https = $secure;
		return strtr($this->options->controllerClass, 
			array(
				':module' => $this->moduleName,
				':controller' => $this->controllerName,
				':secure' => $secure ? $this->options->secureClass : '',
			)
		);
	}

	public function makeControllerClassPath($className)
	{
		$appPath = Gongo_App::$environment->path->app;
		return $appPath . Gongo_File_Path::make('/' . strtr($className, array('_' => '/'))) . '.php';
	}
	
	public function makeSubmitButtonName($request)
	{
		$submitRegex = $this->options->submitRegex;
		foreach ($request as $key => $value) {
			if ($key === '__url__') continue;
			if (preg_match($submitRegex, $key, $matches)) {
				$this->buttonName = $matches[0];
				$submit = Gongo_Str::snakeToPascal($matches[1]);
				if ($submit) return $submit;
			}
		}
		return '';
	}
	
	public function submitName($request = null)
	{
		if (is_null($request)) return $this->submitName;
		return $this->makeSubmitButtonName($request);
	}
	
	public function makeControllerMethodName($action, $method, $request)
	{
		$method = strtolower($method);
		$action = Gongo_Str::snakeToCamel($action);
		$this->actionName = $action;
		$submit = $this->makeSubmitButtonName($request);
		$this->submitName = $submit;
		return $method . ucfirst($action) . $submit;
	}

	public function prepareClassInfo($module, $controller, $action, $method, $request, $secure)
	{
		$className = $this->makeControllerClassName($module, $controller, $secure);
		$classPath = $this->makeControllerClassPath($className);
		$methodName = $this->makeControllerMethodName($action, $method, $request);
		return array($className, $classPath, $methodName);
	}
	
	public function executeController($app, $controller, $e = array())
	{
		$eAction = isset($e['action']) ? $e['action'] : $app->args->__action__ ;
		$eMethod = strtolower( isset($e['method']) ? $e['method'] : $app->server->REQUEST_METHOD );
		$request = array();
		if ($eMethod === 'get') {
			$request = $app->get->_();
		} else if ($eMethod === 'post') {
			$request = $app->post->_();
		}
		$eRequest = isset($e['submit']) ? array_merge($request, array($e['submit'] => '')) : $request ;
		$eSecure = isset($e['https']) ? $e['https'] : $app->env()->path->https ;

		$this->currentAction = $eAction;
		$methodName = $this->makeControllerMethodName($eAction, $eMethod, $eRequest);
		
		if ($this->buttonName) {
			if ($eMethod === 'get') {
				unset($app->get->{$this->buttonName});
			} else if ($eMethod === 'post') {
				unset($app->post->{$this->buttonName});
			}
		}
		$methodName .= $eSecure ? $this->options->secureMethod : '' ;
		if (!method_exists($controller, $methodName)) return $app->error('404');

		$this->controller = $controller;
		$this->methodName = $methodName;
		$argStr = $app->args->__args__;

		if ($this->options->useExtension && preg_match('/^(\.(?:' . $this->options->extension . '))/', $argStr, $m)) {
			$argStr = substr($argStr, strlen($m[1]));
			$app->args->__args__ = $argStr;
			$this->currentExtension = $m[1];
		}
		$this->currentArgs = $argStr;
		
		$sep = $this->options->argSeparator;
		$refl = new ReflectionMethod($controller, $methodName);
		$numOfRequiredParams = $refl->getNumberOfRequiredParameters();

		if ($argStr == '') {
			if ($numOfRequiredParams != 1) return $app->error('404');
			return call_user_func(array($controller, $methodName), $app);
		}

		if (!Gongo_Str::startsWith($argStr, $sep)) return $app->error('404');
		
		$argArr = array_map('urldecode', explode($sep, substr($argStr, 1)));
		$args = array_merge(array($app), $argArr);
		
		if (count($args) < $numOfRequiredParams) return $app->error('404');
		
		foreach ($refl->getParameters() as $p) {
			$pos = $p->getPosition();
			if ($pos !== 0) {
				$name = $p->getName();
				$app->args->{$name} = isset($args[$pos]) ? $args[$pos] : null ;
				$this->currentArgMaps[$pos] = ':' . $name;
			}
		}
		ksort($this->currentArgMaps);
		return call_user_func_array(array($controller, $methodName), $args);
	}

	public function executeIndexController($app, $controller, $e = array())
	{
		$e['action'] = $this->options->indexAction;
		return $this->executeController($app, $controller, $e);
	}

	public function getArgs($current = true)
	{
		if ($current) return $this->currentAction();
		$args = implode('/', $this->currentArgMaps());
		return $args != '' ? '/' . $args : '' ;
	}

	public function getAction($args = false, $current = true)
	{
		$action = $this->currentAction();
		$extension = $this->currentExtension();
		$argString = $args ? $this->getArgs($current) : '' ;
		return $action . $extension . $argString;
	}

	public function getActionPath($args = false)
	{
		$basepath = $this->basepath();
		$action = $this->getAction($args);
		return $basepath . '/' . $action;
	}

	public function path($app, $query = null, $args = null, $action = null, $basepath = null, $type = 0, $short = false)
	{
		$args = is_null($args) ? $this->getArgs(false) : $args ;
		$action = is_null($action) ? $this->getAction(false) : $action ;
		$basepath = is_null($basepath) ? $this->basepath() : $basepath ;
		if (is_array($query)) {
			$query = $app->url->buildQuery($query);
		}
		$query = is_null($query) ? '??' : $query ;
		return $app->url->path($basepath . '/' . $action . $args . $query, $short, $type);
	}
}


class Gongo_App_Wrapper_Post extends Gongo_Bean_ArrayWrapper
{
	public function __construct() 
	{
		$this->_data = &$_POST;
	}
}


class Gongo_App_Url_Router extends Gongo_App_Base
{
	public $uses = array(
		'-mountPoint' => '',
	);
	public $url;
	public $method;
	public $conditions;
	public $params = array();
	public $match = false;
	public $serverReqUri;
	public $requestUrl;
	public $requestMethod;
	public $numericPrefix = '';
	public $argSeparator = '&';

	public function __construct($options = array())
	{
		if (Gongo_App::$environment) {
			$options = $this->defaultValue($options, '-mountPoint', Gongo_App::$environment->path->mountPoint);
		}
		parent::__construct($options);
	}

	public function init($app)
	{
		$mountPoint = $this->options->mountPoint;
		$this->requestMethod = $app->server->REQUEST_METHOD;
		$this->serverReqUri = $app->server->REQUEST_URI;
		$this->requestUrl = str_replace($mountPoint, '', $this->serverReqUri);
		return $this;
	}

	public function match($httpMethod, $url, $conditions=array(), $mountPoint = null)
	{
		$requestUri = is_null($mountPoint) ? $this->requestUrl : str_replace($mountPoint, '', $this->serverReqUri) ;
		$requestMethod = $this->requestMethod;
		$this->method = strtoupper($httpMethod);
		$this->url = $url;
		$this->conditions = $conditions;
		$this->match = false;
		$httpMethods = explode('|', strtoupper($httpMethod));
		if ($httpMethod === '*' || in_array($requestMethod, $httpMethods)) {
			$paramNames = array();
			$paramValues = array();
			preg_match_all('@:([a-zA-Z0-9_]+)@', $url, $paramNames, PREG_PATTERN_ORDER);
			$paramNames = $paramNames[1];
			$regexedUrl = preg_replace_callback('@:[a-zA-Z0-9_]+@', array($this, 'regexValue'), $url);
			if (preg_match('@^' . $regexedUrl . '(?:\?.*)?$@', $requestUri, $paramValues)) {
				array_shift($paramValues);
				foreach ($paramNames as $i => $paramName) {
					$this->params[$paramName] = rawurldecode($paramValues[$i]);
				}
				$this->match = true;
			}
		}
		return $this->match;
	}

	public function path($path, $short = false, $type = 0)
	{
		$root = '';
		if (!$short) {
			if ($type === 0) $root = Gongo_App::$environment->path->rootUrl;
			if ($type === 1) $root = Gongo_App::$environment->path->rootUrlHttps;
			if ($type === 2) $root = Gongo_App::$environment->path->rootUrlHttp;
		}
		return $root . $this->options->mountPoint . $path;
	}

	protected function regexValue($matches)
	{
		$key = strtr($matches[0], array(':' => ''));
		if (array_key_exists($key, $this->conditions)) {
			return '(' . $this->conditions[$key] . ')';
		} else {
			return '([a-zA-Z0-9_]+)';
		}
	}

	public function buildUrl($arr, $shortUrl = true)
	{
		$url = '';
		if (isset($arr['scheme'])) $url .= $arr['scheme'] . '://';
		if (isset($arr['user'])) {
			$url .= $arr['user'];
			if (isset($arr['pass'])) $url .= ':' . $arr['pass'];
			$url .= '@';
		}
		if (isset($arr['host'])) $url .= $arr['host'];
		if (isset($arr['path'])) $url .= $arr['path'];
		if (isset($arr['query']) && $arr['query'] !== '') $url .= '?' . $arr['query'];
		if (isset($arr['fragment'])) $url .= '#' . $arr['fragment'];
		if (!$shortUrl && strpos($url, '/', 0) === 0) {
			$url = $this->path($url);
		}
		return $url;
	}

	public function buildQuery($aQuery = array(), $prefix = null, $sep = null)
	{
		$prefix = is_null($prefix) ? $this->numericPrefix : $prefix ;
		$sep = is_null($sep) ? $this->argSeparator : $sep ;
		return http_build_query($aQuery, $prefix , $sep);
	}

	public function replaceQueryArgs($url = null, $newQuery = array(), $hash = null, $shortUrl = true) 
	{
		if (is_null($url)) return $this->requestUrl;
		$existsQueryTag = strpos($url, '??') !== false;
		$url = strtr($url, array('??' => '?'));
		list($aQuery, $aUrl) = $this->extractQuery($url, true);
		list($aReqQuery, $aReqUrl) = $this->extractQuery(null, true);
		if ($existsQueryTag) {
			$aQuery = array_merge($aReqQuery, $aQuery);
			$aUrl['query'] = $this->buildQuery($aQuery);
		}
		if (!empty($newQuery)) {
			$aQuery = array_merge($aQuery, $newQuery);
			$aUrl['query'] = $this->buildQuery($aQuery);
		}
		if (!is_null($hash)) {
			$aUrl['fragment'] = $hash;
		}
		return $this->buildUrl($aUrl, $shortUrl);
	}

	public function extractQuery($url = null, $retUrl = false)
	{
		$url = is_null($url) ? $this->requestUrl : $url ;
		$aUrl = parse_url($url);
		$aQuery = array();
		if (isset($aUrl['query'])) {
			parse_str($aUrl['query'], $aQuery);
		}
		return $retUrl ? array($aQuery, $aUrl) : $aQuery ;
	}

	public function initRoute($app, $aComponents, $sPath = '', $aConditions = array(), $obj = null)
	{
		$obj = is_null($obj) ? $app : $obj ;
		foreach ($aComponents as $route => $controller) {
			if (strpos($route, '/', 0) === 0) {
				$route = substr($route, 1);
				if ($this->match('*', "{$sPath}/{$route}/.*")) {
					$obj->{$route}->init($app,"{$sPath}/{$route}", $aConditions, $obj);
				}
			}
		}
	}

	public function replacePathArgs($path, $args, $qargs = array(), $hash = null, $shortUrl = true)
	{
		if (strpos($path, ':') !== false) {
			$params = array();
			foreach ($args as $k => $v) {
				$params[':'.$k] = $v;
			}
			$path = strtr($path, $params);
		}
		return $this->replaceQueryArgs($path, $qargs, $hash, $shortUrl);
	}
	
	public function requestUrl($query = false)
	{
		return $query ? $this->requestUrl : Gongo_App::$environment->path->requestUrl ;
	}
}

//!count(debug_backtrace()) and require dirname(__FILE__) . "/../gongo.php";
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

//!count(debug_backtrace()) and Sloth::doctest(__FILE__);

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

class Gongo_App_Wrapper_Server extends Gongo_Bean_ArrayWrapper 
{
	public function __construct() 
	{
		$this->_data = &$_SERVER;
	}
}

class Gongo_App_View extends Gongo_App_Base
{
	public $uses = array(
		'template' => null,
		'context' => 'Gongo_Bean',
	);

	public function __construct($aComponents = array())
	{
		parent::__construct($aComponents);
		$this->afterInit('context', $this->_initContext());
	}

	function initContext($obj)
	{
		$obj->view = $this;
		return $obj;
	}

	function beforeRender($app, $viewname, $context = array()) 
	{
		return array($viewname, $context);
	}
	
	public function render($app, $viewname, $context = array())
	{
		list($viewname, $context) = $this->beforeRender($app, $viewname, $context);
		$context = array_merge($this->context->_(), $context);
		return $app->render($viewname, $context, $this->template);
	}
}

class Gongo_App_Wrapper_Session extends Gongo_Bean_ArrayWrapper
{
	public function __construct()
	{
		$this->_data = &$_SESSION;
	}

	public function __set($key, $value)
	{
		if (is_null($value)) {
			unset($this->_data[$key]);
			return $value;
		} else {
			$this->_data[$key] = $value;
			return $value;
		}
	}
}

class Gongo_App_Wrapper_Get extends Gongo_Bean_ArrayWrapper
{
	public function __construct()
	{
		$this->_data = &$_GET;
	}
}

class Gongo_App_Html_AbstractTemplate extends Gongo_App_Base
{
	public $templateType = 'php';
	public $uses = array(
		'-dirpath' => null,
		'-filename' => null,
	);

	public function __construct($options = array())
	{
		$this->setPathObj();
		$options = $this->defaultValue($options, '-dirpath', 
			Gongo_App::$environment->path->{$this->templateType}->templatePath
		);
		parent::__construct($options);
	}

	public function setPathObj()
	{
		Gongo_App::$environment->path->_($this->templateType, $this->_initPath());
	}

	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_Template', Gongo_App::$environment->path, $this->templateType);
	}

	public function dirpath($value = null)
	{
		if (is_null($value)) return $this->options->dirpath;
		$this->options->dirpath = $value;
		return $this;
	}

	public function name($value = null)
	{
		if (is_null($value)) return $this->options->filename;
		$this->options->filename = $value;
		return $this;
	}

	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.php');
		extract($context);
		ob_start();
		include($filepath);
		return ob_get_clean();
	}

	public function render($context, $filename = null)
	{
		if (isset($context['layout']) && !empty($context['layout'])) {
			$context['content'] = $this->renderTemplate($context, $filename);
			return $this->renderTemplate($context, $context['layout']);
		}
		return $this->renderTemplate($context, $filename);
	}
}

class Gongo_File
{
	static function makeDir($path, $mode = 0777, $recursive = true)
	{
		if (file_exists($path)) return $path;
		if (!mkdir($path, $mode, $recursive)) {
			throw new Gongo_File_Exception("failed to create " . $path);
		}
		return $path;
	}

	static function dirSize($path, $readable = false) 
	{
		$option = $readable ? '-sh' : '-sb' ;
		$result = exec('du ' . $option . ' ' . $path);
		if (preg_match('/^([^\s]+)\s/', trim($result), $match)) {
			return $match[1];
		}
		return false;
	}

	static function rmDir($path, $delete = true, $top = null) 
	{
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		$top = is_null($top) ? $path : $top ;
		$handle = opendir($path);
		if (!$handle) return false;
		while (false !== ($file = readdir($handle))) {
			if ($file === '.' || $file === '..') continue;
			$target = $path . DIRECTORY_SEPARATOR . $file;
			if (is_file($target)) {
				unlink($target);
			} else if (is_dir($target)) {
				self::rmDir($target, $delete, $top);
			}
		}
		if ($path !== $top) rmdir($path);
		if ($path === $top && $delete) rmdir($top);
		return true;
	}

	static function mv($src, $dst) 
	{
		return rename($src, $dst);
	}

	static function rm($path, $terminate = true) 
	{
		$path = is_array($path) ? $path : array($path) ;
		foreach ($path as $file) {
			if (is_file($file)) {
				if (!unlink($file) && $terminate) return false;
			} else if (is_dir($file)) {
				if (!self::rmDir($file) && $terminate) return false;
			}
		}
		return true;
	}

	static function cpDir($src, $dst, $overwrite = false)
	{
		$success = true;
		if (!file_exists($src)) return false;
		if (!file_exists($dst)) self::makeDir($dst);
		if (!is_dir($dst)) return false;
		$dstpath = rtrim($dst, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Gongo_File_Path::basename($src);
		if (!$overwrite && file_exists($dstpath)) return false;
		if (is_file($src)) {
			if (!copy($src, $dstpath)) return false;
		} else if (is_dir($src)) {
			self::makeDir($dstpath);
			$src = rtrim($src, DIRECTORY_SEPARATOR);
			foreach (scandir($src) as $filename) {
				if ($filename === '.' || $filename === '..') continue;
				if (strpos($filename, '.') === 0) continue;
				if (!self::cpDir($src . DIRECTORY_SEPARATOR . $filename, $dstpath, $overwrite)) $success = false;
			}
		}
		return $success;
	}

	static function mvDir($src, $dst, $overwrite = false)
	{
		$success = true;
		if (!file_exists($src)) return false;
		if (!file_exists($dst)) self::makeDir($dst);
		if (!is_dir($dst)) return false;
		$dstpath = rtrim($dst, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . Gongo_File_Path::basename($src);
		if (!$overwrite && file_exists($dstpath)) return false;
		if (is_file($src)) {
			if (!self::mv($src, $dstpath)) return false;
		} else if (is_dir($src)) {
			self::makeDir($dstpath);
			$src = rtrim($src, DIRECTORY_SEPARATOR);
			foreach (scandir($src) as $filename) {
				if ($filename === '.' || $filename === '..') continue;
				if (strpos($filename, '.') === 0) continue;
				if (!self::mvDir($src . DIRECTORY_SEPARATOR . $filename, $dstpath, $overwrite)) $success = false;
			}
			self::rmDir($src);
		}
		return $success;
	}
	
	static function iter($path)
	{
		return Sloth::iter(Gongo_Locator::get('DirectoryIterator', $path));
	}

	static function files($path, $files, $sort = "name") 
	{
		$path = rtrim($path, DIRECTORY_SEPARATOR);
		$result = array();
		$isName = $sort === 'name';
		foreach ($files as $name) {
			$filepath = $path . DIRECTORY_SEPARATOR . $name ;
			$stat = stat($filepath);
			$value = $isName ? $name : $stat[$sort] ;
			if ($sort === 'size' && is_dir($filepath)) $value = 0;
			$result[$name] = $value ;
		}
		return $result;
	}
	
	static function scandir($path, $order = 0, $sort = 'name', $context = null) 
	{
		$files = is_null($context) ? scandir($path, $order) : scandir($path, $order, $context) ;
		if ($sort !== 'name') {
			$files = self::files($path, $files, $sort);
			if ($order) {
				arsort($files);
			} else {
				asort($files);
			}
			$files = array_keys($files);
		}
		return $files;
	}
	
	static function scanDirIter($path, $order = 0, $sort = 'name', $context = null)
	{
		$files = self::scandir($path, $order, $sort, $context);
		return Sloth::iter($files);
	}

	static function readableSize($size, $round = 1, $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB')) 
	{
		$mod = 1024;
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		return round($size, $round) . ' ' . $units[$i];
	}
}
