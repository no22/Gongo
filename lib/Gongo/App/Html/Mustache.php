<?php
class Gongo_App_Html_Mustache extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'mustache';
	public $uses = array(
		'renderer' => 'Mustache_Engine',
	);

	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_Mustache', Gongo_App::$environment->path, $this->templateType);
	}

	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.html');
		$template = file_get_contents($filepath);
		return $this->renderer->render($template, $context);
	}
}
