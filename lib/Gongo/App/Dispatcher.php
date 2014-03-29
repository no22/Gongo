<?php
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

