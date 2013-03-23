<?php
class Gongo_App_HttpError extends Gongo_App_Base
{
	protected $httpError = array(
		'404' => 'HTTP/1.0 404 Not Found',
		'500' => 'HTTP/1.0 500 Server Error',
		'401' => 'HTTP/1.0 401 Unauthorized',
		'403' => 'HTTP/1.0 403 Forbidden',
	);

	public function render($app, $err, $fnCallback = false)
	{
		$header = isset($this->httpError[$err]) ? $this->httpError[$err] : "HTTP/1.0 {$err}" ;
		header($header);
		$output = $fnCallback ? Gongo_Fn::call($fnCallback) : null ;
		if (is_null($output) && Gongo_App::cfg()) {
			$layout = Gongo_App::cfg()->Error->layout;
			$context = array();
			if ($layout) {
				$context['layout'] = $layout;
			}
			echo $app->render($err, $context, $app->errorTemplate);
		} else {
			$output = $output ? $output : $header ;
			echo $output ;
		}
		die();
	}
}
