<?php
class Gongo_App_Html_Phamlp extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'phamlp';
	public $uses = array(
		'renderer' => 'HamlParser',
		'cache' => 'Gongo_File_Cache',
	);
	
	public function initPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_Phamlp', Gongo_App::$environment->path, $this->templateType);
	}

	public function compile($filepath, $cachepath)
	{
		$compiled = $this->renderer->parse($filepath);
		return file_put_contents($cachepath, $compiled, LOCK_EX);
	}
	
	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filepath = $this->options->dirpath . Gongo_File_Path::make($filename . '.haml');
		$cachepath = Gongo_App::$environment->path->phamlp->cachePath . Gongo_File_Path::make($filename . '.php');
		$this->cache->updateFile($filepath, $cachepath, $this->_compile());
		extract($context);
		ob_start();
		include($cachepath);
		return ob_get_clean();
	}
}
