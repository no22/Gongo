<?php
class Gongo_App_Form extends Gongo_App_Container 
{
	function makeConfirmSessionName($sessionName, $suffix = 'ConfirmData')
	{
		return $sessionName . $suffix;
	}

	function saveConfirmData($app, $sessionName, $bean, $suffix = 'ConfirmData')
	{
		$data = $bean instanceof Gongo_Bean ? $bean->_() : $bean ;
		$sessionName = $this->makeConfirmSessionName($sessionName, $suffix);
		$app->session->{$sessionName} = $data;
	}

	function loadConfirmData($app, $sessionName, $suffix = 'ConfirmData')
	{
		$sessionName = $this->makeConfirmSessionName($sessionName, $suffix);
		return $app->session->{$sessionName};
	}

	function loadForm($app, $bean) 
	{
		$data = $bean instanceof Gongo_Bean ? $bean->_() : (array) $bean ;
		return Gongo_Bean::cast($this, $data);
	}

	function importForm($app, $mapper, $bean = null, $converter = null) 
	{
		$bean = $bean ? $bean : $mapper->emptyBean() ;
		if (!is_null($converter)) {
			$form = $converter->import($this, $bean);
		} else if (!is_null($mapper) && $mapper->__converter) {
			$form = $mapper->converter->import($this, $bean);
		} else {
			$form = Gongo_Bean::cast($this, $bean, false, true);
		}
		return $form;
	}

	function exportForm($app, $mapper, $bean = null, $converter = null) 
	{
		$bean = $bean ? $bean : $mapper->emptyBean() ;
		if (!is_null($converter)) {
			$form = $converter->export($this, $bean);
		} else if (!is_null($mapper) && $mapper->__converter) {
			$bean = $mapper->converter->export($this, $bean);
		} else {
			$bean = Gongo_Bean::cast($bean, $this, false, true);
		}
		return $bean;
	}
	
	function restore($app, $mapper, $id, $sessionName = '', $converter = null, $errorName = 'validationError', $suffix = 'ConfirmData')
	{
		$sessionName = $sessionName != '' ? $sessionName : $mapper->options->table ;
		if ($app->error->{$errorName}) {
			$form = $this->loadForm($app, $app->error->{$errorName}->postdata);
		} else {
			$confirmData = $this->loadConfirmData($app, $sessionName);
			if ($confirmData) {
				$form = $this->loadForm($app, $confirmData);
				$this->saveConfirmData($app, $sessionName, null, $suffix);
			} else {
				$bean = $id instanceof Gongo_Bean ? $id : $mapper->readBean($app, $id) ;
				$form = $this->importForm($app, $mapper, $bean, $converter);
			}
		}
		return $form;
	}

	function restoreForm($app, $controller, $id, $mapperName = 'mapper', $converterName = 'converter', $errorName = 'validationError', $suffix = 'ConfirmData') 
	{
		$sessionName = $controller->options->id ? $controller->options->id : get_class($controller) ;
		$converter = $controller->{$converterName};
		$mapper = $controller->{$mapperName};
		return $this->restore($app, $mapper, $id, $sessionName, $converter, $errorName, $suffix);
	}
	
	function exportBean($app, $mapper, $bean, $converter = null)
	{
		$data = $bean instanceof Gongo_Bean ? $bean->_() : $bean ;
		$form = $this->loadForm($app, $data);
		return $this->exportForm($app, $mapper, $form, $converter);
	}
}
