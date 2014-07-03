<?php
class Gongo_App_Ajax extends Gongo_App_Base
{
	public $uses = array(
		'xml' => null,
		'json' => null,
		'dispatcher' => null,
		'-mimeType' => array(
			'json' => 'application/json',
			'jsonp' => 'text/javascript',
			'xml' => 'application/xml',
			'php' => 'application/php',
			'debug' => 'application/php',
		),
		'-debug' => null,
		'-default' => 'json',
		'-defaultMimeType' => 'application/octet-stream',
		'-extension' => 'jsonp|json|php|debug',
		'-jsonp' => 'callback',
		'-jsonpCallback' => 'jsonpCallback',
		'-jsonpRegex' => '/^[a-zA-Z0-9_]+$/',
		'-xml' => 'result',
		'-xmlHttpRequestOnly' => true,
		'-allowOrigin' => false,
		'-allowCredentials' => false,
		'-allowedOrigin' => false,
		'-useHttpHost' => true,
	);

	function setExtensions($app, $dispatcher = null)
	{
		if (!$this->isValidRequest($app)) $app->error('403');
		$dispatcher = is_null($dispatcher) ? $app->dispatcher : $dispatcher ;
		$dispatcher->allowedExtensions($this->options->extension);
		$this->dispatcher = $dispatcher;
		if (is_null($this->options->debug)) {
			$this->options->debug = $app->env()->development;
		}
	}

	function getExtension($ext = null)
	{
		$default = $this->options->default;
		if ($this->dispatcher) {
			$ext = is_null($ext) ? $this->dispatcher->currentExtension($default) : $ext ;
		} else {
			$ext = is_null($ext) ? $default : $ext ;
		}
		return $ext;
	}

	function header($ext = 'json', $charset = 'utf-8')
	{
		header('X-Content-Type-Options: nosniff');
		if ($this->options->allowOrigin) {
			header('Access-Control-Allow-Origin: ' . $this->options->allowOrigin);
		}
		if ($this->options->allowCredentials) {
			header('Access-Control-Allow-Credentials: true');
		}
		$ext = $ext[0] === '.' ? substr($ext, 1) : $ext ;
		if ($this->options->debug) {
			header("Content-type: text/plain; charset={$charset}");
			return;
		}
		$mimeType = $this->options->mimeType;
		$mime = isset($mimeType[$ext]) ? $mimeType[$ext] : $this->options->defaultMimeType ;
		header("Content-type: {$mime}; charset={$charset}");
	}

	function encodeJson($data)
	{
		if (!$this->json) return json_encode($data);
		return $this->json->encode($data);
	}

	function encodeJsonp($data, $callback)
	{
		$encoded = $this->encodeJson($data);
		return $callback . '(' . $encoded . ')';
	}

	function encodePhp($data)
	{
		return serialize($data);
	}

	function encodeDebug($data)
	{
		return print_r($data, true);
	}

	function encodeXml($data, $root)
	{
		$root = $root ? $root : 'root' ;
		return $this->xml->encode($data, $root);
	}

	function encode($data, $ext = 'json', $text = null)
	{
		$ext = $ext[0] === '.' ? substr($ext, 1) : $ext ;
		if ($ext === 'json') return $this->encodeJson($data);
		if ($ext === 'jsonp') return $this->encodeJsonp($data, $text);
		if ($ext === 'php') return $this->encodePhp($data);
		if ($ext === 'debug') return $this->encodeDebug($data);
		if ($ext === 'xml') return $this->encodeXml($data, $text);
	}

	function isValidRequest($app)
	{
		if ($this->options->xmlHttpRequestOnly && $app->server->HTTP_X_REQUESTED_WITH !== 'XMLHttpRequest') {
			return false;
		}
		$origin = $app->server->HTTP_ORIGIN;
		$allowedOrigin = $this->options->allowedOrigin;
		$host = $app->env()->path->rootUrl;
		if (!$origin) return true;
		if (!$allowedOrigin) {
			if ($this->options->useHttpHost) return $host === $origin;
			return true;
		}
		return $origin === $allowedOrigin;
	}

	function response($app, $data, $ext = null, $text = null)
	{
		$ext = strtolower($this->getExtension($ext));
		$ext = $ext[0] === '.' ? substr($ext, 1) : $ext ;
		if ($ext === 'xml') {
			$text = $text ? $text : $this->options->xml ;
		} else if ($ext === 'jsonp') {
			$text = $text ? $text : trim($app->request->{$this->options->jsonp}) ;
			$text = $text ? $text : $this->options->jsonpCallback ;
			if (!preg_match($this->options->jsonpRegex, $text)) {
				$app->error('403');
			}
		}
		$this->header($ext);
		return $this->encode($data, $ext, $text);
	}
}
