<?php
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
			$this->url->initRoute($this, $this->componentClasses(), $path, array());
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
