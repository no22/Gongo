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
		'-tableAlias' => array(),
		'-autoPopulate' => true,
		'-autoIncrement' => true,
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

	function namedScopes($scopes = null)
	{
		return $this->options->namedScopes;
	}

	function afterInitMapper($obj)
	{
		$obj->db($this->db);
		$obj->table($this->options->table);
		$obj->entityClass($this->options->entityClass);
		$obj->primaryKey($this->options->pk);
		$obj->namedScopes($this->options->namedScopes);
		$obj->tableAlias($this->options->tableAlias);
		$obj->autoPopulate($this->options->autoPopulate);
		$obj->autoIncrement($this->options->autoIncrement);
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

	function tableName()
	{
		return $this->mapper->tableName();
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

	function defaultTableAlias($value = null)
	{
		if (is_null($value)) return $this->options->defaultTableAlias;
		$this->options->defaultTableAlias = $value;
		return $this;
	}

	function getRelationMapperInstance($class)
	{
		return Gongo_Locator::get($class);
	}

	function pkId($relMapper)
	{
		return $this->identifier($relMapper->options->pk);
	}

	function join($keys, $q = null, $inner = false)
	{
		return Gongo_Db_Mapper::joinHandler($this, $keys, $q, $inner);
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
