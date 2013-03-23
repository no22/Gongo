<?php
class Gongo_App_Path extends Gongo_Component_Container
{
	public $root;
	
	function __construct($root)
	{
		$this->root = $root;
	}
	
	function _app()
	{
		return $this->root . Gongo_File_Path::make('/app');
	}
	
	function _temp()
	{
		$path = $this->root . Gongo_File_Path::make('/work');
		Gongo_File::makeDir($path);
		return $path;
	}
	
	function _sessionSavePath($path)
	{
		return $this->root . Gongo_File_Path::make($path);
	}
	
	function _template()
	{
		return $this->root . Gongo_File_Path::make('/template');
	}
	
	function _config()
	{
		return $this->root . Gongo_File_Path::make('/config');
	}

	function _configFile()
	{
		return $this->config . Gongo_File_Path::make('/config.ini');
	}

	function _configDevelopment()
	{
		return $this->config . Gongo_File_Path::make('/development.ini');
	}

	function _configProduction()
	{
		return $this->config . Gongo_File_Path::make('/production.ini');
	}

	function _log()
	{
		$path = $this->temp . Gongo_File_Path::make('/logs');
		Gongo_File::makeDir($path);
		return $path;
	}

	function _logFile()
	{
		return $this->log . Gongo_File_Path::make('/log_' . date('Y-m-d') . '.txt');
	}

	function _sqlLogFile()
	{
		return $this->log . Gongo_File_Path::make('/sqllog_' . date('Y-m-d') . '.txt');
	}

	function _webapp()
	{
		return dirname(dirname($this->root));
	}

	function _lib()
	{
		return $this->webapp . Gongo_File_Path::make('/lib');
	}
	
	function _home()
	{
		return dirname($this->webapp);
	}
	
	function _htmlPath()
	{
		return Gongo_Locator::get('Gongo_App_Path_DocumentRoot', $this);
	}

	function _html()
	{
		return $this->htmlPath->html;
	}

	function _css()
	{
		return $this->htmlPath->css;
	}

	function _js()
	{
		return $this->htmlPath->js;
	}

	function _img()
	{
		return $this->htmlPath->img;
	}
	
	function _domain()
	{
		return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '' ;
	}

	function _port()
	{
		return isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '' ;
	}

	function _https() 
	{
		return isset($_SERVER['HTTPS']) && filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN);
	}
	

	function _scheme()
	{
		return 'http' . ($this->https ? 's' : '') ;
	}

	function _rootUrl()
	{
		$scheme = $this->scheme;
		$httpHost = $this->domain;
		$port = $this->port;
		return $scheme . '://' . $httpHost . $port ;
	}

	function _rootUrlHttp()
	{
		$httpHost = $this->domain;
		$port = $this->port;
		return 'http://' . $httpHost . $port ;
	}
	
	function _rootUrlHttps()
	{
		$httpHost = $this->domain;
		$port = $this->port;
		return 'https://' . $httpHost . $port ;
	}

	function _requestPath()
	{
		$path = isset($_GET['__url__']) ? $_GET['__url__'] : '' ;
		if ($path != '' && !Gongo_Str::startsWith($path, '/')) $path = '/' . $path;
		return $path;
	}

	function _mountPoint()
	{
		list($reqUrl) = explode('?',$_SERVER['REQUEST_URI']);
		if ($reqUrl == '/index.php' && $this->requestPath == '') {
			$this->requestPath = 'index.php' ;
			$mountPoint = '';
		} else if (Gongo_Str::endsWith($reqUrl, '/') &&  $this->requestPath == '') {
			$mountPoint = substr(urldecode($reqUrl), 0, -1) ;
		} else {
			$mountPoint = substr(urldecode($reqUrl), 0, -strlen($this->requestPath));
		}
		return $mountPoint === '/' ? '' : $mountPoint ;
	}

	function _requestUrl()
	{
		return Gongo_Str::startsWith($this->requestPath, '/') ? $this->requestPath : '/' . $this->requestPath ;
	}
	
	function _smarty2()
	{
		return Gongo_Locator::get('Gongo_App_Path_Smarty2', $this);
	}
	
	function _smarty3()
	{
		return Gongo_Locator::get('Gongo_App_Path_Smarty3', $this);
	}

	function _php()
	{
		return Gongo_Locator::get('Gongo_App_Path_Template', $this, 'php');
	}

	function _twig()
	{
		return Gongo_Locator::get('Gongo_App_Path_Twig', $this);
	}
}
