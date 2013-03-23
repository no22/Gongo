<?php
class Application extends Gongo_App
{
	public $uses = array(
		'root' => 'Skelton_Controller_Root',
	);
}

$app = new Application(dirname(__FILE__));
$app->init()->run();
