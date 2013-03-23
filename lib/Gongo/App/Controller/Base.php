<?php
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
