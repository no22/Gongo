<?php
class Gongo_App_Mapper_Habtm extends Gongo_App_Mapper
{
	public $uses = array(
		'-autoPopulate' => false,
		'relation' => 'Gongo_App_Mapper_Relation',
	);

	public function foreignKey($key)
	{
		return $key . '_id';
	}
	
	public function deleteAll($key, $id)
	{
		$key = $this->foreignKey($key);
		$bean = $this->get();
		$bean->{$key} = $id;
		$this->delete($bean);
	}

	protected function prepareBean($bean)
	{
		return $bean;
	}
	
	protected function saveAllRelation($subject, $subjectId, $col, $idlist, $seq = null)
	{
		$subjectKey = $this->foreignKey($subject);
		$colKey = $this->foreignKey($col);
		foreach ($idlist as $i => $id) {
			$obj = $this->prepareBean($this->get());
			$obj->{$subjectKey} = $subjectId;
			$obj->{$colKey} = $id;
			if ($seq) $obj->{$seq} = $i;
			$this->save($obj);
		}
	}

	public function saveAll($bean, $subject, $col, $seq = null, $q = null, $mapper = null)
	{
		// default value
		$q = is_null($q) ? $this->q() : $q ;
		$mapper = is_null($mapper) ? $this->relation->{$subject} : $mapper ;
		$idlist = $bean->{$col};
		$idlist = is_string($idlist) ? array_map('intval', array_map('trim', explode(',', $idlist))) : $idlist ;
		// save bean
		$q->addIgnoreKey($col);
		$result = $mapper->save($bean, $q);
		// delete current relations
		$subjectId = $bean->{$mapper->options->pk};
		$this->deleteAll($subject, $subjectId);
		// save all relations
		$this->saveAllRelation($subject, $subjectId, $col, $idlist, $seq);
		return $result;
	}

	public function prepareFindAllQuery($subject, $subjectId, $col, $seq = null, $qcol = null)
	{
		$query = is_null($qcol) ? $this->q() : $qcol ;
		$subjectKey = $this->identifier($this->foreignKey($subject));
		$fromTableAlias = $this->identifier($this->options->defaultTableAlias);
		$query->ifields($col . '.id AS id')->where("{$fromTableAlias}.{$subjectKey} = :__subjectId__");
		$query->bind(array(':__subjectId__' => $subjectId));
		if ($seq) $query->orderBy($seq);
		return $query;
	}
	
	public function findAll($subject, $subjectId, $col, $seq = null, $q = null, $qcol = null, $mapper = null)
	{
		// default value
		$q = is_null($q) ? $this->q() : $q ;
		$mapper = is_null($mapper) ? $this->relation->{$subject} : $mapper ;
		// load bean
		$bean = $mapper->get($subjectId, $q);
		// prepare query
		$query = $this->prepareFindAllQuery($subject, $subjectId, $col, $seq, $qcol);
		$idlist = array();
		foreach ($query->iter() as $row) {
			$idlist[] = $row['id'];
		}
		$bean->{$col} = $idlist;
		return $bean;
	}
}
