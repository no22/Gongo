<?php

class Gongo_App_Url_Router extends Gongo_App_Base
{
	public $uses = array(
		'-mountPoint' => '',
	);
	public $url;
	public $method;
	public $conditions;
	public $params = array();
	public $match = false;
	public $serverReqUri;
	public $requestUrl;
	public $requestMethod;
	public $numericPrefix = '';
	public $argSeparator = '&';

	public function __construct($options = array())
	{
		if (Gongo_App::$environment) {
			$options = $this->defaultValue($options, '-mountPoint', Gongo_App::$environment->path->mountPoint);
		}
		parent::__construct($options);
	}

	public function init($app)
	{
		$mountPoint = $this->options->mountPoint;
		$this->requestMethod = $app->server->REQUEST_METHOD;
		$this->serverReqUri = $app->server->REQUEST_URI;
		$this->requestUrl = str_replace($mountPoint, '', $this->serverReqUri);
		return $this;
	}

	public function match($httpMethod, $url, $conditions=array(), $mountPoint = null)
	{
		$requestUri = is_null($mountPoint) ? $this->requestUrl : str_replace($mountPoint, '', $this->serverReqUri) ;
		$requestMethod = $this->requestMethod;
		$this->method = strtoupper($httpMethod);
		$this->url = $url;
		$this->conditions = $conditions;
		$this->match = false;
		$httpMethods = explode('|', strtoupper($httpMethod));
		if ($httpMethod === '*' || in_array($requestMethod, $httpMethods)) {
			$paramNames = array();
			$paramValues = array();
			preg_match_all('@:([a-zA-Z0-9_]+)@', $url, $paramNames, PREG_PATTERN_ORDER);
			$paramNames = $paramNames[1];
			$regexedUrl = preg_replace_callback('@:[a-zA-Z0-9_]+@', array($this, 'regexValue'), $url);
			if (preg_match('@^' . $regexedUrl . '(?:\?.*)?$@', $requestUri, $paramValues)) {
				array_shift($paramValues);
				foreach ($paramNames as $i => $paramName) {
					$this->params[$paramName] = rawurldecode($paramValues[$i]);
				}
				$this->match = true;
			}
		}
		return $this->match;
	}

	public function path($path, $short = false, $type = 0)
	{
		$root = '';
		if (!$short) {
			if ($type === 0) $root = Gongo_App::$environment->path->rootUrl;
			if ($type === 1) $root = Gongo_App::$environment->path->rootUrlHttps;
			if ($type === 2) $root = Gongo_App::$environment->path->rootUrlHttp;
		}
		return $root . $this->options->mountPoint . $path;
	}

	protected function regexValue($matches)
	{
		$key = strtr($matches[0], array(':' => ''));
		if (array_key_exists($key, $this->conditions)) {
			return '(' . $this->conditions[$key] . ')';
		} else {
			return '([a-zA-Z0-9_]+)';
		}
	}

	public function buildUrl($arr, $shortUrl = true)
	{
		$url = '';
		if (isset($arr['scheme'])) $url .= $arr['scheme'] . '://';
		if (isset($arr['user'])) {
			$url .= $arr['user'];
			if (isset($arr['pass'])) $url .= ':' . $arr['pass'];
			$url .= '@';
		}
		if (isset($arr['host'])) $url .= $arr['host'];
		if (isset($arr['path'])) $url .= $arr['path'];
		if (isset($arr['query']) && $arr['query'] !== '') $url .= '?' . $arr['query'];
		if (isset($arr['fragment'])) $url .= '#' . $arr['fragment'];
		if (!$shortUrl && strpos($url, '/', 0) === 0) {
			$url = $this->path($url);
		}
		return $url;
	}

	public function buildQuery($aQuery = array(), $prefix = null, $sep = null)
	{
		$prefix = is_null($prefix) ? $this->numericPrefix : $prefix ;
		$sep = is_null($sep) ? $this->argSeparator : $sep ;
		return http_build_query($aQuery, $prefix , $sep);
	}

	public function replaceQueryArgs($url = null, $newQuery = array(), $hash = null, $shortUrl = true) 
	{
		if (is_null($url)) return $this->requestUrl;
		$existsQueryTag = strpos($url, '??') !== false;
		$url = strtr($url, array('??' => '?'));
		list($aQuery, $aUrl) = $this->extractQuery($url, true);
		list($aReqQuery, $aReqUrl) = $this->extractQuery(null, true);
		if ($existsQueryTag) {
			$aQuery = array_merge($aReqQuery, $aQuery);
			$aUrl['query'] = $this->buildQuery($aQuery);
		}
		if (!empty($newQuery)) {
			$aQuery = array_merge($aQuery, $newQuery);
			$aUrl['query'] = $this->buildQuery($aQuery);
		}
		if (!is_null($hash)) {
			$aUrl['fragment'] = $hash;
		}
		return $this->buildUrl($aUrl, $shortUrl);
	}

	public function extractQuery($url = null, $retUrl = false)
	{
		$url = is_null($url) ? $this->requestUrl : $url ;
		$aUrl = parse_url($url);
		$aQuery = array();
		if (isset($aUrl['query'])) {
			parse_str($aUrl['query'], $aQuery);
		}
		return $retUrl ? array($aQuery, $aUrl) : $aQuery ;
	}

	public function initRoute($app, $aComponents, $sPath = '', $aConditions = array(), $obj = null)
	{
		$obj = is_null($obj) ? $app : $obj ;
		foreach ($aComponents as $route => $controller) {
			if (strpos($route, '/', 0) === 0) {
				$route = substr($route, 1);
				if ($this->match('*', "{$sPath}/{$route}/.*")) {
					$obj->{$route}->init($app,"{$sPath}/{$route}", $aConditions, $obj);
				}
			}
		}
	}

	public function replacePathArgs($path, $args, $qargs = array(), $hash = null, $shortUrl = true)
	{
		if (strpos($path, ':') !== false) {
			$params = array();
			foreach ($args as $k => $v) {
				$params[':'.$k] = $v;
			}
			$path = strtr($path, $params);
		}
		return $this->replaceQueryArgs($path, $qargs, $hash, $shortUrl);
	}
	
	public function requestUrl($query = false)
	{
		return $query ? $this->requestUrl : Gongo_App::$environment->path->requestUrl ;
	}
}
