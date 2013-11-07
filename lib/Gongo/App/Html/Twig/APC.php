<?php
class Gongo_App_Html_Twig_APC extends Gongo_App_Html_Twig
{
	public $uses = array(
		'renderer' => 'Twig_Environment_Cache_APC',
		'-prefix' => 'twig',
	);

	public function initTwigRenderer($twig)
	{
		$twig->setLoader($this->loader);
		$twig->setCache($this->options->prefix);
		if (Gongo_App::$environment->development) {
			$twig->enableAutoReload();
		}
		return $twig;
	}
}
