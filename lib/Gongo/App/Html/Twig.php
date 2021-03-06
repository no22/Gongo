<?php
class Gongo_App_Html_Twig extends Gongo_App_Html_AbstractTemplate
{
	public $templateType = 'twig';
	public $uses = array(
		'loader' => array('Twig_Loader_Filesystem', array()),
		'renderer' => 'Twig_Environment',
	);

	public function __construct($options = array())
	{
		parent::__construct($options);
		$this->afterInit('loader', $this->_initTwigLoader());
		$this->beforeInit('renderer', $this->_injectTwigLoader());
		$this->afterInit('renderer', $this->_initTwigRenderer());
	}

	public function setPathObj()
	{
		// do nothing
	}

	public function initTwigLoader($loader)
	{
		$loader->setPaths(substr($this->dirpath(), 0, -1));
		return $loader;
	}

	public function injectTwigLoader($args)
	{
		$args[0] = $this->loader;
		return $args;
	}

	public function initTwigRenderer($twig)
	{
		$cachePath = Gongo_App::$environment->path->twig->cachePath;
		$cachePath = $cachePath ? substr($cachePath, 0, -1) : false ;
		$twig->setCache($cachePath);
		if (Gongo_App::$environment->development) {
			$twig->enableAutoReload();
		}
		return $twig;
	}

	public function renderTemplate($context, $filename = null)
	{
		$filename = is_null($filename) ? $this->options->filename : $filename ;
		$filename .= '.html' ;
		return $this->renderer->render($filename, $context);
	}
}
