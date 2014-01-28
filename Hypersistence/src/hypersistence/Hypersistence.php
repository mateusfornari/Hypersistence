<?php

class HypersistenceLazyLoad {

	private $className;
	private $var;
	private $value;

	public function __construct(&$var, $className) {
		$this->className = $className;
		$this->var = &$var;
		$this->value = null;
	}

	public function __get($name) {
		$obj = new $this->className();
		$objPkVar = &$obj->getPkVar();
		$objPkVar = $this->value;
		$obj->load();
		$this->var = &$obj;
		return $obj->$name;
	}
	public function __set($name, $value) {
		if(!$this->list){
			$obj = new $this->className();
			$objPkVar = &$obj->getPkVar();
			$objPkVar = $this->value;
			$obj->load();
			$this->var = &$obj;
			$obj->$name = $value;
		}else{
			$this->var = $value;
		}
	}
	
	public function __call($name, $arguments) {
		$obj = new $this->className();
		$objPkVar = &$obj->getPkVar();
		$objPkVar = $this->value;
		$obj->load();
		$this->var = &$obj;
		return $obj->$name($arguments);
	}
	
	public function setHypersistenceLazyLoadValue($value){
		$this->value = $value;
	}
	public function getHypersistenceLazyLoadValue(){
		return $this->value;
	}
}

class HypersistenceEntity {

	private $object;
	private $table;
	private $fk;
	private $vars = array();

	public function __construct($ClassName, $table, $fk = null) {
		$this->object = $ClassName;
		$this->table = $table;
		$this->fk = $fk;
	}

	public function getObject() {
		return $this->object;
	}

	public function getTable() {
		return $this->table;
	}

	public function getFk() {
		return $this->fk;
	}

	public function setObject($object) {
		$this->object = $object;
	}

	public function setTable($table) {
		$this->table = $table;
	}

	public function setFk($fk) {
		$this->fk = $fk;
	}

	
	/**
	 * Binds a property to its referred database column as primary key.
	 * @param mixed $var The property reference.
	 * @param string $dbColumn The name of referred database column.
	 * @throws Exception Throws an exception if the passed class does not exist.
	 */
	public function bindPk(&$var, $dbColumn){
		$this->bindVar($var, $dbColumn, true);
	}
	
	/**
	 * Binds a property to its referred database column if it is an object.
	 * @param mixed $var The property reference.
	 * @param string $dbColumn The name of referred database column.
	 * @param string $className The class name of the object.
	 * @throws Exception Throws an exception if the passed class does not exist.
	 */
	public function bindManyToOne(&$var, $dbColumn, $className){
		$this->bindVar($var, $dbColumn, false, $className);
	}
	
	/**
	 * Binds a property to its referred database column.
	 * @param mixed $var The property reference.
	 * @param string $dbColumn The name of referred database column.
	 * @param boolean $isPrimaryKey Pass true if it is the primary key of database table.
	 * @param string $className If the value of the property is an object pass the class name.
	 * @throws Exception Throws an exception if the passed class does not exist.
	 */
	public function bindVar(&$var, $dbColumn, $isPrimaryKey = false, $className = null) {
		$index = sizeof($this->vars);
		if (!is_null($className)) {
			if (!class_exists($className)) {
				throw new Exception("The class $className is not defined!");
			}else{
				$refClass = new ReflectionClass($className);
				if($refClass->isSubclassOf('Hypersistence')){
					$var = new HypersistenceLazyLoad($var, $className);
				}
			}
		}
		$this->vars[$index]['var'] = &$var;
		$this->vars[$index]['col'] = $dbColumn;
		$this->vars[$index]['pk'] = $isPrimaryKey;
		$this->vars[$index]['class'] = $className;
	}

	public function getVars() {
		return $this->vars;
	}
	
	public function getPkColumn() {
		foreach ($this->vars as $v) {
			if ($v['pk'])
				return $v['col'];
		}
		return false;
	}

	public function &getPkVar() {
		foreach ($this->vars as $v) {
			if ($v['pk'])
				return $v['var'];
		}
		return false;
	}
	
}

class Hypersistence {

	/**
	 * @var array|HypersistenceEntity
	 */
	private $entities = array();
	

	/**
	 * @var DB
	 */
	protected $conn = null;

	

	/**
	 * Binds the entity to its referred table in database.
	 * @param mixed $object The instance of the entity object, use $this.
	 * @param string $tableName The name of referred table in database.
	 * @param string $fk If the entity inherits another pass the name of foreign key column.
	 */
	public function bindEntity($className, $tableName, $foreignKey = null) {
		$this->conn = DB::getDBConnection();
		$this->entities[$className] = new HypersistenceEntity($className, $tableName, $foreignKey);
		return $this->entities[$className];
	}

	/**
	 * @return void
	 */
	private function orderEntities(){
		$classes = array_keys($this->entities);
		$count = sizeof($classes);
		for($i = 0; $i < $count; $i++){
			if(!is_numeric($classes[$i])){
				$rc1 = new ReflectionClass($classes[$i]);
				for($j = 1; $j < $count; $j++){
					$rc2 = new ReflectionClass($classes[$j]);
					if($rc2->isSubclassOf($rc1->name)){
						$aux = $classes[$i];
						$classes[$i] = $classes[$j];
						$classes[$j] = $aux;
					}
				}
			}
		}
		$orderedEntities = array();
		for($i = 0; $i < $count; $i++){
			$orderedEntities[$i] = $this->entities[$classes[$i]];
		}
		$this->entities = $orderedEntities;
	}
	
	public function getEntities() {
		return $this->entities;
	}

		
	/**
	 * Loads data from database and populates the object.
	 * @return boolean
	 */
	public function load() {
		$this->orderEntities();
		$joins = array();
		$filter = array();
		$bounds = array();
		$fields = array();
		$count = sizeof($this->entities);
		for($i = 0; $i < $count; $i++){
			$e = $this->entities[$i];
			$joins[] = $e->getTable();
			if($i == 0){
				$filter[] = $e->getTable().'.'.$e->getPkColumn().' = :'.$e->getTable().'_'.$e->getPkColumn();
				$bounds[':'.$e->getTable().'_'.$e->getPkColumn()] = $e->getPkVar();
			}
			if($count > 1 && $i < $count - 1)
				$filter[] = $e->getTable().'.'.$e->getFk().' = '.$this->entities[$i + 1]->getTable().'.'.$this->entities[$i + 1]->getPkColumn();
			
			$vars = $e->getVars();
			foreach ($vars as $v){
				if(!is_null($v['col']))
					$fields[] = $e->getTable().'.'.$v['col'].' AS '.$e->getTable().'_'.$v['col'];
			}
		}
		
		$sql = 'SELECT '.implode(', ', $fields).' FROM '.implode(', ', $joins).' WHERE '.implode(' AND ', $filter);
		
		if($stmt = $this->conn->prepare($sql)){
			if($stmt->execute($bounds) && $stmt->rowCount() > 0){
				$result = $stmt->fetchObject();
				foreach ($this->entities as $e){
					$vars = $e->getVars();
					foreach ($vars as $v){
						if(!is_null($v['col'])){
						$column = $e->getTable().'_'.$v['col'];
							if(!is_null($v['class']) && is_a($v['var'], 'HypersistenceLazyLoad')){
								$v['var']->setHypersistenceLazyLoadValue($result->$column);
							}else{
								$v['var'] = $result->$column;
							}
						}
					}
				}
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Saves object data in database.
	 * @return boolean
	 */
	public function save(){
		$this->orderEntities();
		$entities = array_reverse($this->entities);
		
		$lastEntity = null;
		foreach ($entities as $e){
			
			$fields = array();
			$values = array();
			$bounds = array();
			if(is_null($e->getPkVar())){
				$vars = $e->getVars();
				foreach ($vars as $v){
					if(!$v['pk']){
						$fields[] = $v['col'];
						$values[] = ':'.$v['col'];
						if(is_object($v['var']) && is_a($v['var'], 'Hypersistence'))
							$bounds[':'.$v['col']] = $v['var']->getPkVar();
						else if(is_object($v['var']) && is_a($v['var'], 'PercistenciaLazyLoad'))
							$bounds[':'.$v['col']] = $v['var']->getHypersistenceLazyLoadValue();
						else
							$bounds[':'.$v['col']] = $v['var'];
					}
				}
				
				if(!is_null($lastEntity)){
					$fields[] = $e->getFk();
					$values[] = ':'.$e->getFk();
					$bounds[':'.$e->getFk()] = $lastEntity->getPkVar();
				}
				
				$sql = 'INSERT INTO '.$e->getTable().' ('.implode(', ', $fields).') VALUES('.  implode(', ', $values).')';
				
				if($stmt = $this->conn->prepare($sql)){
					if($stmt->execute($bounds)){
						$pk = &$e->getPkVar();
						$pk = $this->conn->lastInsertId();
						
						unset($pk);
					}else{
						return false;
					}
				}else{
					return false;
				}
				$lastEntity = $e;
			}else{
				$vars = $e->getVars();
				$pk = 'id = :id';
				foreach ($vars as $v){
					if(!$v['pk']){
						$fields[] = $v['col'].' = :'.$v['col'];
						if(is_object($v['var']) && is_a($v['var'], 'Hypersistence'))
							$bounds[':'.$v['col']] = $v['var']->getPkVar();
						else if(is_object($v['var']) && is_a($v['var'], 'HypersistenceLazyLoad'))
							$bounds[':'.$v['col']] = $v['var']->getHypersistenceLazyLoadValue();
						else
							$bounds[':'.$v['col']] = $v['var'];
					}else{
						$pk = $v['col'].' = :'.$v['col'];
						$bounds[':'.$v['col']] = $v['var'];
					}
				}
				
				$sql = 'UPDATE '.$e->getTable().' SET '.implode(', ', $fields).' WHERE '.$pk;
				
				if($stmt = $this->conn->prepare($sql)){
					if(!$stmt->execute($bounds)){
						return false;
					}
				}else{
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * 
	 * @return HypersistenceResultSet
	 */
	public function search(){
		$this->orderEntities();
		return new HypersistenceResultSet($this, $this->entities, $this->conn);
	}
	
	/**
	 * 
	 * @param ResultSet $stmt
	 * @return array
	 */
	public function getHypersistenceList(ResultSet $stmt){
		$list = array();
		$class = $this->entities[0]->getObject();
		while($result = $stmt->fetchObject()){
			$obj = new $class();
			$entities = &$obj->entities;
			foreach ($entities as $e){
				$vars = $e->getVars();
				foreach ($vars as $v){
					$column = $e->getTable().'_'.$v['col'];
					if(!is_null($v['class']) && is_a($v['var'], 'HypersistenceLazyLoad')){
						$v['var']->setHypersistenceLazyLoadValue($result->$column);
					}else{
						$v['var'] = $result->$column;
					}
				}
			}
			$list[] = $obj;
		}
		return $list;
	}
	
	private function getPkColumn() {
		$this->orderEntities();
		if (sizeof($this->entities) == 0) {
			return false;
		} else {
			foreach ($this->entities[0]->getVars() as $v) {
				if ($v['pk'])
					return $v['col'];
			}
			return false;
		}
	}
	
	public function &getPkVar() {
		$this->orderEntities();
		if (sizeof($this->entities) == 0) {
			return false;
		} else {
			foreach ($this->entities[0]->getVars() as $v) {
				if ($v['pk'])
					return $v['var'];
			}
			return false;
		}
	}

}

class HypersistenceResultSet{
	
	private $rows;
	private $offset;
	private $page;
	private $entities;
	private $totalRows;
	private $totalPages;
	private $conn;
	private $resultList;
	/**
	 * @var Hypersistence
	 */
	private $persistenciaObject;
	
	public function __construct(&$persistenciaObject, &$entities, $conn) {
		$this->rows = 0;
		$this->offset = 0;
		$this->page = 0;
		$this->entities = &$entities;
		$this->totalRows = 0;
		$this->totalPages = 0;
		$this->conn = $conn;
		$this->resultList = array();
		$this->persistenciaObject = &$persistenciaObject;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function execute(){
		
		$this->totalRows = 0;
		$this->totalPages = 0;
		$this->resultList = array();
		
		$joins = array();
		$filter = array();
		$bounds = array();
		$fields = array();
		$count = sizeof($this->entities);
		for($i = 0; $i < $count; $i++){
			$e = $this->entities[$i];
			$joins[] = $e->getTable();
			
			if($count > 1 && $i < $count - 1)
				$filter[] = $e->getTable().'.'.$e->getFk().' = '.$this->entities[$i + 1]->getTable().'.'.$this->entities[$i + 1]->getPkColumn();
			
			$vars = $e->getVars();
			foreach ($vars as $v){
				$fields[] = $e->getTable().'.'.$v['col'].' AS '.$e->getTable().'_'.$v['col'];
				
				if(!is_null($v['var'])){
					if(is_object($v['var']) && is_a($v['var'], 'Hypersistence')){
						$bounds[':'.$e->getTable().'_'.$v['col']] = $v['var']->getPkVar();
						$like = '=';
					}else if(is_object($v['var']) && is_a($v['var'], 'HypersistenceLazyLoad')){
						$bounds[':'.$e->getTable().'_'.$v['col']] = $v['var']->getHypersistenceLazyLoadValue();
						$like = '=';
					}else if(is_numeric($v['var'])){
						$bounds[':'.$e->getTable().'_'.$v['col']] = $v['var'];
						$like = '=';
					}else{
						$bounds[':'.$e->getTable().'_'.$v['col']] = '%'.$v['var'].'%';
						$like = 'like';
					}
					$filter[] = $e->getTable().'.'.$v['col'].' '.$like.' :'.$e->getTable().'_'.$v['col'];
				}
				
			}
		}
		
		$where = sizeof($filter) > 0 ? ' WHERE '.implode(' AND ', $filter) : '';
		
		$sql = 'SELECT COUNT(*) AS total FROM '.implode(', ', $joins).$where;
		
		if($stmt = $this->conn->prepare($sql)){
			if($stmt->execute($bounds) && $stmt->rowCount() > 0){
				$result = $stmt->fetchObject();
				$this->totalRows = $result->total;
				$this->totalPages = $this->rows > 0 ? ceil($this->totalRows / $this->rows) : 1;
			}else{
				return false;
			}
		}
		
		$offset = $this->page > 0 ? ($this->page - 1) * $this->rows : $this->offset;
		$bounds[':offset'] = array($offset, PDO::PARAM_INT);
		
		$bounds[':limit'] = array(intval($this->rows > 0 ? $this->rows : $this->totalRows), PDO::PARAM_INT);
		
		$sql = 'SELECT '.implode(', ', $fields).' FROM '.implode(', ', $joins).$where.' LIMIT :limit OFFSET :offset';
		
		if($stmt = $this->conn->prepare($sql)){
			if($stmt->execute($bounds) && $stmt->rowCount() > 0){
				$this->resultList = $this->persistenciaObject->getHypersistenceList($stmt);
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
		return false;
	}
	
	public function setRows($rows) {
		$this->rows = $rows >= 0 ? $rows : 0;
	}

	public function setOffset($offset) {
		$this->offset = $offset >= 0 ? $offset : 0;
	}

	public function setPage($page) {
		$this->page = $page >= 0 ? $page : 0;
	}
	
	public function getTotalRows() {
		return $this->totalRows;
	}

	public function getTotalPages() {
		return $this->totalPages;
	}

	public function getResultList() {
		return $this->resultList;
	}

}
?>