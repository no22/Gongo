<?php
class Gongo_App_Html_Dwoo extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'dwoo';
	public $uses = array(
		'renderer' => 'Dwoo',
	);

	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_Doo', Gongo_App::$environment->path, $this->templateType);
	}
	
	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.tpl');
		return $this->renderer->get($filepath, $context);
	}
}
