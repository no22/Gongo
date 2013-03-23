<?php
class Gongo_App_Html_Fammel extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'fammel';
	public $uses = array(
		'renderer' => 'Fammel',
		'cache' => 'Gongo_File_Cache',
	);

	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_Fammel', Gongo_App::$environment->path, $this->templateType);
	}
	
	public function compile($text)
	{
		$renderer = new Fammel();
		$renderer->parse($text);
		return $renderer->render();
	}
	
	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.haml');
		$cachepath = Gongo_App::$environment->path->fammel->cachePath . Gongo_File_Path::make($filename . '.php');
		$this->cache->update($filepath, $cachepath, $this->_compile());
		extract($context);
		ob_start();
		include($cachepath);
		return ob_get_clean();
	}
}
