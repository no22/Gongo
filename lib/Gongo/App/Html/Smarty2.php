<?php
class Gongo_App_Html_Smarty2 extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'smarty2';
	public $uses = array(
		'renderer' => 'Smarty',
	);

	public function __construct($options = array())
	{
		parent::__construct($options);
		$this->afterInit('renderer', $this->_initSmarty());
	}

	public function setPathObj()
	{
		// do nothing
	}

	public function initSmarty($smarty)
	{
		$envpath = Gongo_App::$environment->path;
		if ($this->dirpath()) {
			$smarty->template_dir = Gongo_File_Path::make($this->dirpath());
		} else {
			$smarty->template_dir = $envpath->smarty2->templatePath;
		}
		$smarty->compile_dir = $envpath->smarty2->compiledTemplatePath;
		$smarty->config_dir = $envpath->smarty2->configPath;
		$smarty->cache_dir = $envpath->smarty2->cachePath;
		return $smarty;
	}

	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$this->renderer->assign($context);
		return $this->renderer->fetch($filename . '.tpl');
	}
}
