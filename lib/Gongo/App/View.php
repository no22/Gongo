<?php
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
