<?php
class Gongo_App_ErrorHandler extends Gongo_App_Base
{
	public $uses = array(
		'-errorReportingLevel' => array(
			E_ERROR, E_USER_ERROR,
		),
	);

	protected $errorLevel = array(
		E_ERROR => 'Fatal Error',
		E_WARNING => 'Warning',
		E_NOTICE => 'Notice',
		E_USER_ERROR => 'Fatal Error',
		E_USER_WARNING => 'Warning',
		E_USER_NOTICE => 'Notice',
		E_STRICT => 'Suggestion',
	);

	public function error($app, $aError, $isDevelopment = false)
	{
		$errno = $aError['errno'];
		if (in_array($errno, $this->options->errorReportingLevel) || $isDevelopment) {
			$callback = $isDevelopment ? $this->_outputError($app, $aError, $isDevelopment) : false ;
			return $app->error('500', $callback);
		}
		return $isDevelopment;
	}
	
	public function outputError($app, $aError, $isDevelopment = false)
	{
		$aError['isDev'] = $isDevelopment;
		$aError['type'] = isset($this->errorLevel[$aError['errno']]) ? $this->errorLevel[$aError['errno']] : 'Error' ;
		if (Gongo_App::cfg()->Error->layout) {
			$aError['layout'] = Gongo_App::cfg()->Error->layout;
		}
		return $app->render('error', $aError, $app->errorTemplate);
	}

 	public function handleError($app, $errno, $message, $file, $line)
	{
		$aError = compact('errno', 'message', 'file', 'line');
		Gongo_App::cfg()->Debug->use_debug_mail(false) and $app->log($aError, Gongo_App::cfg()->Debug->email);
		Gongo_App::cfg()->Debug->use_debug_log(false) and $app->log($aError);
		return $this->error($app, $aError, $app->env()->development);
	}
}
