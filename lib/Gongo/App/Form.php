<?php
class Gongo_App_Form extends Gongo_App_Container 
{
	function makeConfirmSessionName($controller, $suffix = 'ConfirmData')
	{
		return $controller->options->id . $suffix;
	}

	function saveConfirmData($app, $controller, $bean, $suffix = 'ConfirmData')
	{
		$data = $bean instanceof Gongo_Bean ? $bean->_() : $bean ;
		$sessionName = $this->makeConfirmSessionName($controller, $suffix);
		$app->session->{$sessionName} = $data;
	}

	function loadConfirmData($app, $controller, $suffix = 'ConfirmData')
	{
		$sessionName = $this->makeConfirmSessionName($controller, $suffix);
		return $app->session->{$sessionName};
	}

	function loadForm($app, $controller, $bean) 
	{
		$data = $bean instanceof Gongo_Bean ? $bean->_() : $bean ;
		return $controller->mapper->newBean($data, $this);
	}

	function importForm($app, $controller, $bean = null, $converter = null) 
	{
		$bean = $bean ? $bean : $controller->mapper->emptyBean() ;
		if (!is_null($converter)) {
			$form = $converter->import($this, $bean);
		} else if ($controller->__converter) {
			$form = $controller->converter->import($this, $bean);
		} else {
			$form = Gongo_Bean::cast($this, $bean, false, true);
		}
		return $form;
	}

	function exportForm($app, $controller, $bean = null, $converter = null) 
	{
		$bean = $bean ? $bean : $controller->mapper->emptyBean() ;
		if (!is_null($converter)) {
			$form = $converter->export($this, $bean);
		} else if ($controller->__converter) {
			$bean = $controller->converter->export($this, $bean);
		} else {
			$bean = Gongo_Bean::cast($bean, $this, false, true);
		}
		return $bean;
	}
	
	function restore($app, $controller, $id, $errorName = 'validationError', $suffix = 'ConfirmData')
	{
		if ($app->error->{$errorName}) {
			$form = $this->loadForm($app, $controller, $app->error->{$errorName}->postdata);
		} else if ($this->loadConfirmData($app, $controller)) {
			$form = $this->loadForm($app, $controller, $this->loadConfirmData($app, $controller));
			$this->saveConfirmData($app, $controller, null, $suffix);
		} else {
			$bean = $id instanceof Gongo_Bean ? $id : $controller->mapper->readBean($app, $id) ;
			$form = $this->importForm($app, $controller, $bean);
		}
		return $form;
	}

	function exportBean($app, $controller, $bean)
	{
		$data = $bean instanceof Gongo_Bean ? $bean->_() : $bean ;
		$form = $this->loadForm($app, $controller, $data);
		return $this->exportForm($app, $controller, $form);
	}
}
