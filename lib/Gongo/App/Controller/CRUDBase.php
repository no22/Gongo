<?php
class Gongo_App_Controller_CRUDBase extends Gongo_App_Controller
{
	public $uses = array(
		'mapper' => null,
		'form' => 'Gongo_App_Form',
		'converter' => null,
		'filter' => null,
		'token' => 'Gongo_App_Token',
		'paginator' => 'Gongo_App_Paginator',
		'validator' => array(
			'Gongo_App_Validator',
			array(
				'-redirect' => null,
				'-rules' => array(
				),
			),
		),
		'-id' => '',
		'-view' => '',
		'-secureAction' => 'edit|confirm',
		'-validateAction' => 'edit|confirm',
		'-filterAction' => 'edit|confirm',
	);

	function __construct($aComponents = array())
	{
		parent::__construct($aComponents);
		$this->afterInit('validator', $this->_initValidator());
	}

	function initValidator($obj)
	{
		return $obj;
	}

	protected function listQuery()
	{
		return $this->mapper->q();
	}

	function getIndex($app)
	{
		list($paginator, $list) = $this->paginator->paginate($app, $this->listQuery());
		return $this->render($app, '/index', compact('list','paginator'));
	}

	function getEdit($app)
	{
		$form = $this->form->restore($app, $this->mapper, $app->get->id, $this->options->id, $this->converter);
		return $this->render($app, '/edit', compact('form'));
	}

	function postEditSubmit($app)
	{
		$bean = $this->form->exportBean($app, $this->mapper, $app->post, $this->converter);
		$this->mapper->writeBean($app, $bean);
		$this->redirect($app, '/index');
	}

	function postEditCancel($app)
	{
		$this->form->saveConfirmData($app, $this->options->id, $app->post);
		$this->redirect($app, '/edit??');
	}

	function postEditDelete($app)
	{
		$id = $app->post->id;
		$this->mapper->deleteBean($app, $id);
		$this->redirect($app, '/index');
	}

	function postConfirmSubmit($app)
	{
		$form = $this->form->loadForm($app, $app->post);
		return $this->render($app, '/confirm', compact('form'));
	}

	function postConfirmCancel($app)
	{
		$this->redirect($app, '/index');
	}

	function postConfirmDelete($app)
	{
		$id = $app->post->id;
		$this->mapper->deleteBean($app, $id);
		$this->redirect($app, '/index');
	}
}
