<?php
class Skelton_Controller_Root extends Skelton_Controller_Base
{
	public $uses = array(
		'view' => 'Skelton_View_Root',
	);
	
	public function getIndex($app)
	{
		return $this->view->render($app, 'index');
	}
}
