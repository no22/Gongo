<?php
class Gongo_App_ExceptionHandler extends Gongo_App_Base
{
	public function error($app, $aError, $isDevelopment = false)
	{
		if ($isDevelopment) {
			$callback = $isDevelopment ? $this->_outputError($app, $aError, $isDevelopment) : false ;
			return $app->error('500', $callback);
		}
		return $isDevelopment;
	}
	
	public function outputError($app, $aError, $isDevelopment = false)
	{
		$aError['isDev'] = $isDevelopment;
		if (Gongo_App::cfg()->Error->layout) {
			$aError['layout'] = Gongo_App::cfg()->Error->layout;
		}
		return $app->render('exception', $aError, $app->errorTemplate);
	}

 	public function handleException($app, $exception)
	{
		$aError = array(
			'type' => get_class($exception),
			'code' => $exception->getCode(),
			'message' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
		);
		if (Gongo_App::cfg()->Debug->use_debug_trace(false)) {
			$aError['trace'] = $this->getTrace($exception);
		}
		Gongo_App::cfg()->Debug->use_debug_mail(false) and $app->log($aError, Gongo_App::cfg()->Debug->email);
		Gongo_App::cfg()->Debug->use_debug_log(false) and $app->log($aError);
		return $this->error($app, $aError, $app->env()->development);
	}

	protected function getTrace($exception) 
	{
		return $exception->getTraceAsString();
	}
}
