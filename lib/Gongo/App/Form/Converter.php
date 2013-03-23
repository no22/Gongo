<?php
class Gongo_App_Form_Converter extends Gongo_App_Base 
{
	public $uses = array(
		'converter' => null,
		'import' => null,
		'export' => null,
		'filter' => null,
		'-import' => array(),
		'-export' => array(),
		'-filter' => array(),
		'-defaultEntityClass' => 'Gongo_Bean',
	);
	
	public function export($form, $bean, $cast = true, $strict = false, $unset = true)
	{
		$bean = is_array($bean) ? Gongo_Locator::get($this->options->defaultEntityClass, $bean) : $bean ;
		$filters = empty($this->options->export) ? null : $this->options->export ;
		if ($this->__export) return $this->export->convert($form, $bean, $cast, $strict, $unset);
		return $this->converter->convert($form, $bean, $filters, $cast, $strict, $unset);
	}

	public function import($form, $bean, $cast = true, $strict = false, $unset = true)
	{
		$bean = is_array($bean) ? Gongo_Locator::get($this->options->defaultEntityClass, $bean) : $bean ;
		$filters = empty($this->options->import) ? null : $this->options->import ;
		if ($this->__import) return $this->import->convert($bean, $form, $cast, $strict, $unset);
		return $this->converter->convert($bean, $form, $filters, $cast, $strict, $unset);
	}

	public function filter($app, $src = null, $dst = null, $cast = false, $strict = false, $unset = true)
	{
		$filters = empty($this->options->filter) ? null : $this->options->filter ;
		$src = is_null($src) ? $app->post : $src ;
		$src = is_array($src) ? Gongo_Locator::get($this->options->defaultEntityClass, $src) : $src ;
		$dst = is_null($dst) ? $src : $dst ;
		$dst = is_array($dst) ? Gongo_Locator::get($this->options->defaultEntityClass, $dst) : $dst ;
		if ($this->__filter) return $this->filter->convert($src, $dst, $filters, $cast, $strict, $unset);
		return $this->converter->convert($src, $dst, $filters, $cast, $strict, $unset);
	}
	
	public function inputFilter($app, $src = null)
	{
		$this->filter($app, $src);
	}
}
