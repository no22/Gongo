<?php
class Gongo_App_Html_Phptal extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'phptal';
	public $uses = array(
		'renderer' => 'PHPTAL',
	);
	
	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_Phptal', Gongo_App::$environment->path, $this->templateType);
	}

	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.html');
		$this->renderer->setTemplate($filepath);
		foreach ($context as $key => $value) {
			$this->renderer->{$key} = $value;
		}
		return $this->renderer->execute();
	}
}
