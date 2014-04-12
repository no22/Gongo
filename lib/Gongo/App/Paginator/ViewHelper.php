<?php
class Gongo_App_Paginator_ViewHelper extends Gongo_App_Base
{
	public $paginator = null;
	public $app = null;
	public $count = null;
	
	public $uses = array(
		'-range' => 2,
	);
	
	function paginator($value = null)
	{
		if (is_null($value)) return $this->paginator ;
		$this->paginator = $value;
		return $this;
	}
	
	function app($value = null)
	{
		if (is_null($value)) return $this->app ;
		$this->app = $value;
		return $this;
	}

	function context($key = null)
	{
		if (is_null($key)) return $this->paginator()->context;
		return $this->paginator()->context->{$key};
	}

	function existsContext($key, $value)
	{
		return in_array($value, $this->paginator()->context->{$key});
	}
	
	function count($value = null)
	{
		if (is_null($value)) return $this->count ;
		$this->count = $value;
		return $this;
	}
	
	function init($app, $paginator, $count) 
	{
		$this->app($app);
		$this->paginator($paginator);
		$this->count($count);
		return $this;
	}
		
	function searchWords() 
	{
		return $this->paginator->context->searchWords;
	}
	
	function column() 
	{
		return $this->paginator->pagingColumn();
	}
	
	function defaultColumn() 
	{
		return $this->paginator->context->defaultOrderColumn;
	}
	
	function direction()
	{
		return $this->paginator->pagingDirection();
	}

	function directionReverse() 
	{
		$dir = $this->direction();
		return $dir === 'desc' ? 'asc' : 'desc' ;
	}

	function sortUrl($col, $dir = null, $args = array(), $hash = null, $short = true)
	{
		if (is_null($dir)) {
			$dir = $col === $this->column() ? $this->directionReverse() : 'asc' ;
		}
		$args = $this->paginator->context->makeQueryParam('orderColumn', $col, $args);
		$args = $this->paginator->context->makeQueryParam('orderDirection', $dir, $args);
		return $this->url($this->pageUrl(), $args, $hash, $short);
	}

	function isSorted($col)
	{
		return $col === $this->column() ? $this->direction() : false ;
	}

	function searchUrl($args = array(), $hash = null, $short = true)
	{
		return $this->url($this->pageUrl(), $args, $hash, $short);
	}

	function currentPage()
	{
		return (int) $this->paginator->context->currentPage;
	}

	function firstPage() 
	{
		return 0;
	}

	function lastPage() 
	{
		return (int) max(floor(($this->count()-1) / $this->paginator->context->pagingSize), 0);
	}
	
	function hasPrev() 
	{
		return $this->currentPage() > $this->firstPage();
	}
	
	function hasNext() 
	{
		return $this->currentPage() < $this->lastPage();
	}
	
	function hasFirst() 
	{
		return $this->currentPage() != $this->firstPage();
	}
	
	function hasLast() 
	{
		return $this->currentPage() != $this->lastPage();
	}

	function prevPage()
	{
		if ($this->hasPrev()) return $this->currentPage() - 1;
		return false;
	}
	
	function nextPage()
	{
		if ($this->hasNext()) return $this->currentPage() + 1;
		return false;
	}
	
	function pageUrl() 
	{
		return $this->paginator->options->url ? $this->paginator->options->url : $this->app()->url->requestUrl() ;
	}
	
	function currentUrl($args = array(), $hash = null, $short = true)
	{
		return $this->url($this->pageUrl(), $args, $hash, $short);
	}
	
	function buildUrl($page, $args = array(), $hash = null, $short = true)
	{
		if ($page !== false) {
			$args = $this->paginator->context->makeQueryParam('currentPage', $page, $args);
			return $this->url($this->pageUrl(), $args, $hash, $short);
		}
		return '';
	}
	
	function firstUrl($args = array(), $hash = null, $short = true)
	{
		return $this->buildUrl($this->firstPage(), $args, $hash, $short);
	}
	
	function lastUrl($args = array(), $hash = null, $short = true)
	{
		return $this->buildUrl($this->lastPage(), $args, $hash, $short);
	}

	function prevUrl($args = array(), $hash = null, $short = true)
	{
		return $this->buildUrl($this->prevPage(), $args, $hash, $short);
	}

	function nextUrl($args = array(), $hash = null, $short = true)
	{
		return $this->buildUrl($this->nextPage(), $args, $hash, $short);
	}

	function pages($args = array(), $hash = null, $short = true)
	{
		$range = $this->options->range;
		$last = $this->lastPage();
		$first = $this->firstPage();
		$page = $this->currentPage();

		$left = max($page - $range, $first);
		$right = min($page + $range, $last);
		if ($page <= $range) {
			$extend = $range - ($page - $left);
			$right = min($page + $range + $extend, $last);
		} else if ($page + $range > $last) {
			$extend = $range - ($last - $page);
			$left = max($page - $range - $extend, $first);
		}
		
		$pages = array();
		if ($last <= $first) return $pages;
		for ($i = $left; $i <= $right; $i++) {
			$bean = Gongo_Locator::get('Gongo_Bean');
			$bean->no = $i + 1;
			$bean->url = $this->buildUrl($i, $args, $hash, $short);
			$bean->current = ($i == $page);
			$pages[$i] = $bean;
		}
		return $pages;
	}

	function url($path, $args = array(), $hash = null, $short = true)
	{
		$currentArgs = $this->paginator->context->getQueryArray($this->app());
		if (is_string($args)) {
			$qargs = array();
			parse_str($args, $qargs);
			$args = $qargs;
		}
		$args = array_merge($currentArgs, $args);
		$path = $this->app()->replacePathArgs($path, $args, $hash);
		return $this->app()->url->path($path, $short);
	}
}
