<?php
class Gongo_App_Mapper extends Gongo_App_Base
{
	public $uses = array(
		'db' => 'Gongo_Db',
		'mapper' => 'Gongo_Db_Mapper',
		'converter' => null,
		'queryWriter' => 'Gongo_Db_QueryWriter',
		'relation' => null,
		'-table' => null,
		'-pk' => 'id',
		'-entityClass' => 'Gongo_Bean',
		'-namedScopes' => array(),
		'-autoPopulate' => true,
		'-createdDateColumn' => 'created',
		'-modifiedDateColumn' => 'modified',
		'-classPrefix' => '',
		'-tablePrefix' => '',
		'-useSqlLog' => false,
		'-defaultTableAlias' => 't',
		'-strict' => null,
	);

	function __construct($options = array())
	{
		parent::__construct($options);
		$this->afterInit('mapper', $this->_afterInitMapper());
		$this->afterInit('db', $this->_afterInitDb());
	}

	function afterInitMapper($obj)
	{
		$obj->db($this->db);
		$obj->table($this->options->table);
		$obj->entityClass($this->options->entityClass);
		$obj->primaryKey($this->options->pk);
		$obj->namedScopes($this->options->namedScopes);
		$obj->autoPopulate($this->options->autoPopulate);
		$obj->createdDateColumn($this->options->createdDateColumn);
		$obj->modifiedDateColumn($this->options->modifiedDateColumn);
		$obj->joinMapper($this);
		if (!is_null($this->options->strict)) {
			$obj->strict($this->options->strict);
		}
		return $obj;
	}

	function afterInitDb($obj)
	{
		$obj->pdo(Gongo_App::$environment->pdo);
		if ($this->options->classPrefix) {
			$obj->classPrefix($this->options->classPrefix);
		}
		if ($this->options->tablePrefix) {
			$obj->tablePrefix($this->options->tablePrefix);
		}
		if ((Gongo_App::$environment->useSqlLog || $this->options->useSqlLog)) {
			$obj->setQueryLog(Gongo_App::$environment->sqlLog);
		}
		return $obj;
	}
		
	function finder($fields = null, $inner = false) { return $this->query($fields, $inner); }
	function q($fields = null, $inner = false) { return $this->query($fields, $inner); }
	function select($fields = null, $inner = false) { return $this->query($fields, $inner); }

	function query($fields = null, $inner = false)
	{
		return $this->mapper->query($fields, $inner);
	}

	function identifier($str) 
	{
		return $this->mapper->identifier($str);
	}
	
	function insert($bean, $q = null)
	{
		return $this->mapper->insert($bean, $q);
	}

	function update($bean, $q = null, $returnRowCount = false)
	{
		return $this->mapper->update($bean, $q, $returnRowCount);
	}
	
	function save($bean, $q = null)
	{
		return $this->mapper->save($bean, $q);
	}

	function delete($id, $q = null, $returnRowCount = false)
	{
		return $this->mapper->delete($id, $q, $returnRowCount);
	}

	function get($id = null, $q = null, $empty = false)
	{
		return $this->mapper->get($id, $q, $empty);
	}

	function foreignKey($key)
	{
		return $key . '_id';
	}

	function getRelationMapper($key) 
	{
		return $this->relation->{$key};
	}

	function join($keys, $q = null, $inner = false) 
	{
		$q = is_null($q) ? $this->q() : $q ;
		$fromTable = $this->identifier($this->options->table);
		$fromAlias = $this->identifier($this->options->defaultTableAlias);
		$q->from($fromTable . " AS {$fromAlias}");
		foreach ($keys as $key => $obj) {
			if (is_int($key)) {
				$relMapper = $this->getRelationMapper($obj);
				$key = $obj;
			} else {
				if (is_string($obj)) {
					$obj = Gongo_Locator::get($obj);
				}
				$relMapper = $obj;
			}
			$joinTable = $this->identifier($relMapper->options->table);
			$joinAlias = $this->identifier($key);
			$pk = $this->identifier($relMapper->options->pk);
			$fkey = $this->identifier($this->foreignKey($key));
			if ($inner) {
				$q->innerJoin("{$joinTable} AS {$joinAlias} ON {$fromAlias}.{$fkey} = {$joinAlias}.{$pk}");
			} else {
				$q->join("{$joinTable} AS {$joinAlias} ON {$fromAlias}.{$fkey} = {$joinAlias}.{$pk}");
			}
		}
		return $q;
	}
	
	function beginTransaction()
	{
		return $this->mapper->beginTransaction();
	}
	
	function commit()
	{
		return $this->mapper->commit();
	}
	
	function rollBack()
	{
		return $this->mapper->rollBack();
	}

	function pdo()
	{
		return $this->mapper->pdo();
	}
	
	function newBean($data, $bean = null)
	{
		$bean = is_null($bean) ? $this->emptyBean() : $bean ;
		if ($data instanceof Gongo_Bean) {
			$data = $data->_();
		} else {
			$data = (array) $data;
		}
		$bean = Gongo_Bean::cast($bean, $data);
		return $bean;
	}

	function emptyBean()
	{
		$bean = $this->get();
		return $bean->__();
	}

	function readBean($app, $id, $q = null) 
	{
		return $id ? $this->get($id, $q, true) : $this->get() ;
	}

	function writeBean($app, $bean, $q = null) 
	{
		return $this->save($bean, $q);
	}

	function deleteBean($app, $id, $q = null, $returnRowCount = false) 
	{
		return $this->delete($id, $q, $returnRowCount);
	}
}
