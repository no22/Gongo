<?php
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
