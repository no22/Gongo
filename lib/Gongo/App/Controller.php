<?php
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
		parent::beforeFilter($app);
		if ($this->__token) {
			$this->viewContext->token = $this->token->token();
		}
		$this->viewContext->controllerId = $this->options->id;
	}

	function tokenError($app)
	{
		return $app->error('403');
	}
}
