<?php
class Gongo_App_Paginator extends Gongo_App_Base
{
	public $query = null;
	public $uses = array(
		'mapper' => null,
		'context' => 'Gongo_App_Paginator_Context',
		'viewHelper' => 'Gongo_App_Paginator_ViewHelper',
		'-sessionName' => 'pagingContext',
		'-searchColumns' => null,
		'-columns' => null,
		'-url' => null,
	);

	function prepare($app, $mapper = null)
	{
		if ($mapper) $this->mapper = $mapper;
		$this->context->setPagingParams($app, $this->options->sessionName);
		return $this;
	}

	function query($value = null)
	{
		if (is_null($value)) return clone $this->query;
		$this->query = $value;
		return $this;
	}

	function pagingOffset()
	{
		$pagingSize = $this->context->pagingSize;
		$currentPage = $this->context->currentPage;
		$offset = $pagingSize * $currentPage;
		return array($offset, $pagingSize);
	}

	function pagingColumn()
	{
		$columns = Gongo_Str::split($this->options->columns);
		$orderColumn = $this->context->orderColumn;
		$orderColumn = $orderColumn ? $orderColumn : $this->context->defaultOrderColumn ;
		if (empty($columns)) return $orderColumn;
		if (in_array($orderColumn, $columns)) return $orderColumn;
		return $this->context->defaultOrderColumn;
	}

	function pagingDirection()
	{
		$dir = strtolower($this->context->orderDirection);
		return $dir === 'desc' ? 'desc' : 'asc' ;
	}

	function searchWords()
	{
		$words = Gongo_Str::replaceSpaces($this->context->searchWords, ' ');
		return Gongo_Str::split($words, ' ');
	}

	function setSearchCondition($q)
	{
		$words = $this->searchWords();
		$columns = Gongo_Str::split($this->options->searchColumns);
		foreach ($words as $i => $word) {
			$q = $this->freeWordCondition($q, $word, $i, $columns);
		}
		return $q;
	}

	protected function escapeMetaChar($word)
	{
		$word = strtr($word, array('%' => '\%', '_' => '\_'));
		return $word;
	}

	function freeWordCondition($q, $word, $i, $columns)
	{
		$cond = array();
		$pattern = '%' . $this->escapeMetaChar($word) . '%';
		$arg = array();
		$j = 0;
		foreach ($columns as $col) {
			$cond['$or'][] = "{$col} LIKE :word__{$i}_{$j}";
			$arg[":word__{$i}_{$j}"] = $pattern;
			$j++;
		}
		$q->where($cond)->bind($arg);
		return $q;
	}

	function conditions($q)
	{
		$q = $this->setSearchCondition($q);
		return $q;
	}

	function setLimit($q)
	{
		list($offset, $limit) = $this->pagingOffset();
		$q->limit($offset, $limit);
		return $q;
	}

	function setOrder($q)
	{
		$col = $this->pagingColumn();
		$dir = $this->pagingDirection();
		$q->orderBy($col . ' ' . $dir);
		return $q;
	}

	function defaultQuery($q = null)
	{
		if (!is_null($q)) return $q;
		$q = $this->query();
		if (!is_null($q)) return $q;
		return $this->mapper->q();
	}

	function current($q = null)
	{
		$q = $this->defaultQuery($q);
		$q = $this->conditions($q);
		$q = $this->setOrder($q);
		$q = $this->setLimit($q);
		return $q->all();
	}

	function count($q = null)
	{
		$q = $this->defaultQuery($q);
		$q = $this->conditions($q);
		return $q->count();
	}

	function paginate($app, $q = null, $mapper = null)
	{
		if (!is_null($q)) $this->query($q);
		$this->prepare($app, $mapper);
		$items = $this->current();
		$count = $this->count();
		$helper = $this->viewHelper->init($app, $this, $count);
		return array($helper, $items);
	}
}
