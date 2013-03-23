<?php
class Gongo_App_Paginator_Context extends Gongo_App_Container
{
	function __()
	{
		parent::__();
		$this->searchWords = '';
		$this->orderColumn = '';
		$this->defaultOrderColumn = 'id';
		$this->orderDirection = 'asc';
		$this->pagingSize = 15;
		$this->currentPage = 0;
		$this->paramNames = array(
			'searchWords'		=> 'q',
			'orderColumn'		=> 's',
			'orderDirection'	=> 'd',
			'pagingSize'		=> 'l',
			'currentPage'		=> 'p',
		);
		return $this;
	}

	function setPagingParam($app, $key, $col)
	{
		$arg = $app->get->{$key};
		if (is_null($arg)) return $this->{$col};
		$this->{$col} = $arg ;
		return $this->{$col};
	}

	function setPagingParams($app, $sessionName, $reset = false)
	{
		$currentSession = $app->session->{$sessionName};
		if (!$reset && $currentSession) Gongo_Bean::cast($this, $currentSession);
		foreach ($this->paramNames as $k => $v) {
			$this->setPagingParam($app, $v, $k);
		}
		$app->session->{$sessionName} = $this;
		return $this;
	}
	
	function setQueryParam($obj, $key, $col, $q = array())
	{
		$arg = $obj->{$col};
		if (is_null($arg)) return $q;
		$q[$key] = $arg;
		return $q;
	}
	
	function getQueryArray($app, $obj = null)
	{
		$obj = is_null($obj) ? $this : $obj ;
		$q = array();
		foreach ($this->paramNames as $k => $v) {
			$q = $this->setQueryParam($obj, $v, $k, $q);
		}
		return $q;
	}

	function getQueryString($app, $obj = null)
	{
		$query = $this->getQueryArray($app, $obj);
		return http_build_query($query);
	}
	
	function makeQueryParam($type, $value, $default = array()) 
	{
		if (!isset($this->paramNames[$type])) return $default;
		$default[$this->paramNames[$type]] = $value;
		return $default;
	}
}
