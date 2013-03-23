<?php
class Gongo_App_Html_H2o extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'h2o';
	public $uses = array(
		'renderer' => 'H2o',
	);
	
	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_H2o', Gongo_App::$environment->path, $this->templateType);
	}

	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.html');
		$this->renderer->loadTemplate($filepath);
		return $this->renderer->render($context);
	}
}
