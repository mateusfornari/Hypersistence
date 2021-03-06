<?php

class HypersistenceEntity
{

    private $className;
    private $table;
    private $fk;
    private $vars = array();
    private $object;

    public function __construct($ClassName, $table, $fk = null, &$object = null)
    {
        $this->className = $ClassName;
        $this->table = $table;
        $this->fk = $fk;
        $this->object = &$object;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getFk()
    {
        return $this->fk;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function setFk($fk)
    {
        $this->fk = $fk;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * Binds a property to its referred database column as primary key.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindPk(&$var, $dbColumn)
    {
        $this->bindVar($var, $dbColumn, true);
    }

    /**
     * Binds a property to its referred database column if it is an object.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param string $className The class name of the object.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindManyToOne(&$var, $dbColumn, $className)
    {
        $this->bindVar($var, $dbColumn, false, $className);
    }

    /**
     * Binds a property to its referred database column if it is an one to many relation.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param string $className The class name of the object.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindOneToMany(&$var, $dbColumn, $className)
    {
        $this->bindVar($var, $dbColumn, false, $className, true);
    }

    /**
     * Binds a property to its referred database column if it is a many to many relation.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param string $className The class name of the object.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindManyToMany(&$var, $relationTable, $dbColumnThis, $dbColumnOther, $className)
    {
        $this->bindVar($var, $dbColumnThis, false, $className, true, $relationTable, $dbColumnOther);
    }

    /**
     * Binds a property to its referred database column.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param boolean $isPrimaryKey Pass true if it is the primary key of database table.
     * @param string $className If the value of the property is an object pass the class name.
     * @param boolean $isList TRUE if the value of the property is a list of objects.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindVar(&$var, $dbColumn, $isPrimaryKey = false, $className = null, $isList = false, $relationTable = null, $dbColumnOther = null)
    {
        $index = sizeof($this->vars);
        if (!is_null($className)) {
            if (!class_exists($className)) {
                throw new Exception("The class $className is not defined!");
            } else {
                $refClass = new ReflectionClass($className);
                if ($refClass->isSubclassOf('Hypersistence')) {
                    if ($isList) {
                        $obj = new $className();
                        foreach ($obj->getEntities() as $e) {
                            foreach ($e->vars as $v) {
                                if ($v['col'] == $dbColumn) {
                                    $v['var'] = $this->object;
                                    break;
                                }
                            }
                        }
                        if (!is_null($relationTable) && !is_null($dbColumnOther)) {
                            $entities = $obj->getEntities();
                            foreach ($entities as $e) {
                                if ($e->className == $className) {
                                    $vars = &$e->vars;
                                    break;
                                }
                            }
                            $i = count($vars);
                            $vars[$i]['var'] = &$this->object;
                            $vars[$i]['col'] = $dbColumn;
                            $vars[$i]['pk'] = $isPrimaryKey;
                            $vars[$i]['class'] = $className;
                            $vars[$i]['list'] = $isList;
                            $vars[$i]['relTable'] = $relationTable;
                            $vars[$i]['colOther'] = $dbColumnOther;
                        }
                        $var = $obj->search();
                    }
                }
            }
        }
        $this->vars[$index]['var'] = &$var;
        $this->vars[$index]['col'] = $dbColumn;
        $this->vars[$index]['pk'] = $isPrimaryKey;
        $this->vars[$index]['class'] = $className;
        $this->vars[$index]['list'] = $isList;
        $this->vars[$index]['relTable'] = $relationTable;
        $this->vars[$index]['colOther'] = $dbColumnOther;
    }

    public function getVars()
    {
        return $this->vars;
    }

    public function getPkColumn()
    {
        foreach ($this->vars as $v) {
            if ($v['pk'])
                return $v['col'];
        }
        return false;
    }

    public function &getPkVar()
    {
        $var = null;
        foreach ($this->vars as $v) {
            if ($v['pk'])
                return $v['var'];
        }
        return $var;
    }

}

class Hypersistence
{

    /**
     * @var array|HypersistenceEntity
     */
    private $entities = array();
    
    private $entitie;
    
    /**
     * Binds the entity to its referred table in database.
     * @param mixed $object The instance of the entity object, use $this.
     * @param string $tableName The name of referred table in database.
     * @param string $fk If the entity inherits another pass the name of foreign key column.
     */
    protected function bindEntity($className, $tableName, $foreignKey = null)
    {
        $this->entities[$className] = new HypersistenceEntity($className, $tableName, $foreignKey, $this);
        $this->entitie = $this->entities[$className];
        return $this->entitie;
    }
    
    /**
     * Binds a property to its referred database column as primary key.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindPk(&$var, $dbColumn)
    {
        $this->entitie->bindVar($var, $dbColumn, true);
    }

    /**
     * Binds a property to its referred database column if it is an object.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param string $className The class name of the object.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindManyToOne(&$var, $dbColumn, $className)
    {
        $this->entitie->bindVar($var, $dbColumn, false, $className);
    }

    /**
     * Binds a property to its referred database column if it is an one to many relation.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param string $className The class name of the object.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindOneToMany(&$var, $dbColumn, $className)
    {
        $this->entitie->bindVar($var, $dbColumn, false, $className, true);
    }

    /**
     * Binds a property to its referred database column if it is a many to many relation.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @param string $className The class name of the object.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindManyToMany(&$var, $relationTable, $dbColumnThis, $dbColumnOther, $className)
    {
        $this->entitie->bindVar($var, $dbColumnThis, false, $className, true, $relationTable, $dbColumnOther);
    }

     /**
     * Binds a property to its referred database column.
     * @param mixed $var The property reference.
     * @param string $dbColumn The name of referred database column.
     * @throws Exception Throws an exception if the passed class does not exist.
     */
    public function bindVar(&$var, $dbColumn)
    {
        $this->entitie->bindVar($var, $dbColumn);
    }
    
    /**
     * @return void
     */
    private function orderEntities()
    {
        $classes = array_keys($this->entities);
        $count = sizeof($classes);
        for ($i = 0; $i < $count; $i++) {
            if (!is_numeric($classes[$i])) {
                for ($j = $i+1; $j < $count; $j++) {
                    $rc1 = new ReflectionClass($classes[$i]);
                    $rc2 = new ReflectionClass($classes[$j]);
                    if ($rc2->isSubclassOf($rc1->name)) {
                        $aux = $classes[$i];
                        $classes[$i] = $classes[$j];
                        $classes[$j] = $aux;
                    }
                }
            }
        }
        $orderedEntities = array();
        for ($i = 0; $i < $count; $i++) {
            $orderedEntities[$i] = $this->entities[$classes[$i]];
        }
        $this->entities = $orderedEntities;
    }

    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Loads data from database and populates the object.
     * @return Hypersistence
     */
    public function load()
    {
        $this->orderEntities();
        $joins = array();
        $filter = array();
        $bounds = array();
        $fields = array();
        $count = sizeof($this->entities);
        for ($i = 0; $i < $count; $i++) {
            $e = $this->entities[$i];
            $joins[] = $e->getTable();
            if ($i == 0) {
                $filter[] = $e->getTable() . '.' . $e->getPkColumn() . ' = :' . $e->getTable() . '_' . $e->getPkColumn();
                $bounds[':' . $e->getTable() . '_' . $e->getPkColumn()] = $e->getPkVar();
            }
            if ($count > 1 && $i < $count - 1)
                $filter[] = $e->getTable() . '.' . $e->getFk() . ' = ' . $this->entities[$i + 1]->getTable() . '.' . $this->entities[$i + 1]->getPkColumn();

            $vars = $e->getVars();
            foreach ($vars as $v) {
                if (!is_null($v['col']) && !$v['list'])
                    $fields[] = $e->getTable() . '.' . $v['col'] . ' AS ' . $e->getTable() . '_' . $v['col'];
            }
        }
        
        $sql = 'SELECT ' . implode(', ', $fields) . ' FROM ' . implode(', ', $joins) . ' WHERE ' . implode(' AND ', $filter);
        if ($stmt = DB::getDBConnection()->prepare($sql)) {
            if ($stmt->execute($bounds) && $stmt->rowCount() > 0) {
                $result = $stmt->fetchObject();
                foreach ($this->entities as $e) {
                    $vars = $e->getVars();
                    foreach ($vars as $v) {
                        if (!is_null($v['col']) && !$v['list']) {
                            $column = $e->getTable() . '_' . $v['col'];
                            if (!is_null($v['class'])) {
                                $class = $v['class'];
								$ref = new ReflectionClass($class);
								if($ref->isSubclassOf('Hypersistence')){
                                    $v['var'] = new $class();
                                    $objPkVar = &$v['var']->getPkVar();
                                    $objPkVar = $result->$column;
                                }
                            } else {
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
     * Verify if exists a register with the primary key of the object.
     * @return boolean
     */
    public function exists()
    {
        $this->orderEntities();
        $joins = array();
        $filter = array();
        $bounds = array();
        $fields = array();
        $count = sizeof($this->entities);
        for ($i = 0; $i < $count; $i++) {
            $e = $this->entities[$i];
            $joins[] = $e->getTable();
            if ($i == 0) {
                $filter[] = $e->getTable() . '.' . $e->getPkColumn() . ' = :' . $e->getTable() . '_' . $e->getPkColumn();
                $bounds[':' . $e->getTable() . '_' . $e->getPkColumn()] = $e->getPkVar();
            }
            if ($count > 1 && $i < $count - 1)
                $filter[] = $e->getTable() . '.' . $e->getFk() . ' = ' . $this->entities[$i + 1]->getTable() . '.' . $this->entities[$i + 1]->getPkColumn();

            $vars = $e->getVars();
            foreach ($vars as $v) {
                if (!is_null($v['col']) && !$v['list'])
                    $fields[] = $e->getTable() . '.' . $v['col'] . ' AS ' . $e->getTable() . '_' . $v['col'];
            }
        }
        
        $sql = 'SELECT ' . implode(', ', $fields) . ' FROM ' . implode(', ', $joins) . ' WHERE ' . implode(' AND ', $filter);

        if ($stmt = DB::getDBConnection()->prepare($sql)) {
            if ($stmt->execute($bounds) && $stmt->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Delete the object from database.
     * @return boolean
     */
    public function delete()
    {
        $this->orderEntities();
        $joins = array();
        $filter = array();
        $bounds = array();
        $fields = array();
        $count = sizeof($this->entities);
        for ($i = 0; $i < $count; $i++) {
            $e = $this->entities[$i];
            $joins[] = $e->getTable();
            if ($i == 0) {
                $filter[] = $e->getTable() . '.' . $e->getPkColumn() . ' = :' . $e->getTable() . '_' . $e->getPkColumn();
                $bounds[':' . $e->getTable() . '_' . $e->getPkColumn()] = $e->getPkVar();
            }
            if ($count > 1 && $i < $count - 1)
                $filter[] = $e->getTable() . '.' . $e->getFk() . ' = ' . $this->entities[$i + 1]->getTable() . '.' . $this->entities[$i + 1]->getPkColumn();
        }

        $sql = 'DELETE ' . implode(', ', $joins) . ' FROM ' . implode(', ', $joins) . ' WHERE ' . implode(' AND ', $filter);

        if ($stmt = DB::getDBConnection()->prepare($sql)) {
            if ($stmt->execute($bounds)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Saves object data in database.
     * @return boolean
     */
    public function save()
    {
        $this->orderEntities();
        $entities = array_reverse($this->entities);
        $lastEntity = null;
        foreach ($entities as $e) {
            $fields = array();
            $values = array();
            $bounds = array();
            if (is_null($e->getPkVar()) || !$e->getObject()->exists()) {
                $vars = $e->getVars();
                foreach ($vars as $v) {
                    if (!$v['pk'] && !$v['list']) {
                        $fields[] = $v['col'];
                        $values[] = ':' . $v['col'];
                        if (is_object($v['var']) && $v['var'] instanceof Hypersistence) {
                            $bounds[':' . $v['col']] = $v['var']->getPkVar();
                        } else {
                            $bounds[':' . $v['col']] = $v['var'];
                        }
                    }
                }

                if (!is_null($lastEntity)) {
                    $fields[] = $e->getFk();
                    $values[] = ':' . $e->getFk();
                    $bounds[':' . $e->getFk()] = $lastEntity->getPkVar();
                }

                $sql = 'INSERT INTO ' . $e->getTable() . ' (' . implode(', ', $fields) . ') VALUES(' . implode(', ', $values) . ')';
                
                if ($stmt = DB::getDBConnection()->prepare($sql)) {
                    if ($stmt->execute($bounds)) {
                        $pk = &$e->getPkVar();
                        if (is_null($pk)) {
                            $pk = DB::getDBConnection()->lastInsertId();
                            unset($pk);
                        }
                    } else {
                        var_dump($stmt->errorInfo());
                        return false;
                    }
                } else {
                    return false;
                }
                $lastEntity = $e;
            } else {
                $vars = $e->getVars();
                $pk = 'id = :id';
                foreach ($vars as $v) {
                    if (!$v['pk'] && !$v['list']) {
                        $fields[] = $v['col'] . ' = :' . $v['col'];
                        if (is_object($v['var']) && $v['var'] instanceof Hypersistence)
                            $bounds[':' . $v['col']] = $v['var']->getPkVar();
                        else
                            $bounds[':' . $v['col']] = $v['var'];
                    }elseif (!$v['list']) {
                        $pk = $v['col'] . ' = :' . $v['col'];
                        $bounds[':' . $v['col']] = $v['var'];
                    }
                }
                
                if(!count($fields)){
                    continue;
                }
                
                $sql = 'UPDATE ' . $e->getTable() . ' SET ' . implode(', ', $fields) . ' WHERE ' . $pk;

                if ($stmt = DB::getDBConnection()->prepare($sql)) {
                    if (!$stmt->execute($bounds)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    public function addManyToManyRelationTo(Hypersistence $object, $relationTable)
    {
        foreach ($this->entities as $e) {
            foreach ($e->getVars() as $v) {
                if (isset($v['relTable']) && $v['relTable'] == $relationTable) {
                    $bounds[':col'] = $this->getPkVar();
                    $bounds[':colOther'] = $object->getPkVar();
                    $sql = "INSERT INTO $relationTable ($v[col], $v[colOther]) VALUES(:col, :colOther)";
                    
                    if ($stmt = DB::getDBConnection()->prepare($sql)) {
                        return $stmt->execute($bounds);
                    }
                }
            }
        }
        throw new Exception('No many to many bounds found!');
        return false;
    }
    
    public function deleteManyToManyRelationTo(Hypersistence $object, $relationTable)
    {
        foreach ($this->entities as $e) {
            foreach ($e->getVars() as $v) {
                if (isset($v['relTable']) && $v['relTable'] == $relationTable) {
                    $bounds[':col'] = $this->getPkVar();
                    $bounds[':colOther'] = $object->getPkVar();
                    $sql = "DELETE FROM $relationTable WHERE $v[col] = :col AND $v[colOther] = :colOther";
                    if ($stmt = DB::getDBConnection()->prepare($sql)) {
                        return $stmt->execute($bounds);
                    }
                }
            }
        }
        throw new Exception('No many to many bounds found!');
        return false;
    }

    /**
     * 
     * @return HypersistenceResultSet
     */
    public function search($orderBy = null)
    {
        $this->orderEntities();
        return new HypersistenceResultSet($this, $this->entities, $orderBy);
    }

    /**
     * 
     * @param ResultSet $stmt
     * @return array
     */
    public function getHypersistenceList(ResultSet $stmt)
    {
        $list = array();
        $className = $this->entities[0]->getClassName();
        
        while ($result = $stmt->fetchObject()) {
            $obj = new $className();
            
            $entities = &$obj->entities;
            foreach ($entities as $e) {
                $vars = $e->getVars();
                foreach ($vars as $v) {
                    if (!$v['list']) {
                        $column = $e->getTable() . '_' . $v['col'];
                        if (!is_null($v['class'])) {
                            $class = $v['class'];
                            $ref = new ReflectionClass($class);
                            if($ref->isSubclassOf('Hypersistence')){
                                $v['var'] = new $class();
                                $objPkVar = &$v['var']->getPkVar();
                                $objPkVar = $result->$column;
                            }
                        } else {
                            $v['var'] = $result->$column;
                        }
                    }
                }
            }
            $list[] = $obj;
        }
        return $list;
    }

    private function getPkColumn()
    {
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

    public function &getPkVar()
    {
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

    public static function commit()
    {
        return DB::getDBConnection()->commit();
    }

    public static function rollBack()
    {
        return DB::getDBConnection()->rollBack();
    }
    
    
}

class HypersistenceResultSet
{

    private $rows;
    private $offset;
    private $page;
    private $entities;
    private $totalRows;
    private $totalPages;
    private $resultList;
    private $orderBy;

    /**
     * @var Hypersistence
     */
    private $persistenciaObject;

    public function __construct(&$persistenciaObject, &$entities, $orderBy)
    {
        $this->rows = 0;
        $this->offset = 0;
        $this->page = 0;
        $this->entities = &$entities;
        $this->totalRows = 0;
        $this->totalPages = 0;
        $this->resultList = array();
        $this->persistenciaObject = &$persistenciaObject;
        $this->orderBy = $orderBy;
    }

    /**
     * 
     * @return boolean
     */
    public function execute()
    {

        $this->totalRows = 0;
        $this->totalPages = 0;
        $this->resultList = array();

        $joins = array();
        $filter = array();
        $bounds = array();
        $fields = array();
        $count = sizeof($this->entities);
        for ($i = 0; $i < $count; $i++) {
            $e = $this->entities[$i];

            $joins[] = $e->getTable();

            if ($count > 1 && $i < $count - 1)
                $filter[] = $e->getTable() . '.' . $e->getFk() . ' = ' . $this->entities[$i + 1]->getTable() . '.' . $this->entities[$i + 1]->getPkColumn();

            $vars = $e->getVars();
            foreach ($vars as $v) {

                if (!$v['list'])
                    $fields[] = $e->getTable() . '.' . $v['col'] . ' AS ' . $e->getTable() . '_' . $v['col'];

                if (!is_null($v['var']) && !$v['list']) {
                    $hasFilter = false;
                    if (is_object($v['var']) && $v['var'] instanceof Hypersistence) {
                        $bounds[':' . $e->getTable() . '_' . $v['col']] = $v['var']->getPkVar();
                        $like = '=';
                        $hasFilter = true;
                    } elseif (is_numeric($v['var'])) {
                        $bounds[':' . $e->getTable() . '_' . $v['col']] = $v['var'];
                        $like = '=';
                        $hasFilter = true;
                    } else {
                        $bounds[':' . $e->getTable() . '_' . $v['col']] = '%'.$v['var'].'%';
                        $like = 'like';
                        $hasFilter = true;
                    }
                    if($hasFilter){
                        $filter[] = $e->getTable() . '.' . $v['col'] . ' ' . $like . ' :' . $e->getTable() . '_' . $v['col'];
                    }
                } elseif (!is_null($v['var'])) {
                    if (isset($v['relTable']) && isset($v['colOther']) && !$v['var'] instanceof HypersistenceResultSet) {
                        $joins[] = $v['relTable'];
                        $filter[] = $v['relTable'] . '.' . $v['colOther'] . ' = ' . $e->getTable() . '.' . $e->getPkColumn();
                        $filter[] = $v['relTable'] . '.' . $v['col'] . ' = :' . $v['relTable'] . '_' . $v['col'];
                        if (is_object($v['var']) && $v['var'] instanceof Hypersistence) {
                            $bounds[':' . $v['relTable'] . '_' . $v['col']] = $v['var']->getPkVar();
                        } else {
                            $bounds[':' . $v['relTable'] . '_' . $v['col']] = $v['var'];
                        }
                        $like = '=';
                    }
                }
            }
        }

        $where = sizeof($filter) > 0 ? ' WHERE ' . implode(' AND ', $filter) : '';

        $sql = 'SELECT COUNT(*) AS total FROM ' . implode(', ', $joins) . $where;

        if ($stmt = DB::getDBConnection()->prepare($sql)) {
            if ($stmt->execute($bounds) && $stmt->rowCount() > 0) {
                $result = $stmt->fetchObject();
                $this->totalRows = $result->total;
                $this->totalPages = $this->rows > 0 ? ceil($this->totalRows / $this->rows) : 1;
            } else {
                return false;
            }
        }

        if($this->orderBy){
            $orderBy = " ORDER BY $this->orderBy";
        }else{
            $orderBy = '';
        }
        
        $offset = $this->page > 0 ? ($this->page - 1) * $this->rows : $this->offset;
        $bounds[':offset'] = array($offset, PDO::PARAM_INT);

        $bounds[':limit'] = array(intval($this->rows > 0 ? $this->rows : $this->totalRows), PDO::PARAM_INT);

        $sql = 'SELECT ' . implode(', ', $fields) . ' FROM ' . implode(', ', $joins) . $where . $orderBy . ' LIMIT :limit OFFSET :offset';
        
        if ($stmt = DB::getDBConnection()->prepare($sql)) {
            if ($stmt->execute($bounds) && $stmt->rowCount() > 0) {
                $this->resultList = $this->persistenciaObject->getHypersistenceList($stmt);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        return false;
    }

    public function fetchAll($orderBy = '')
    {
        $this->rows = 0;
        $this->offset = 0;
        $this->page = 0;
        $this->orderBy = $orderBy;
        if ($this->execute())
            return $this->resultList;
        else
            return array();
    }

    public function setRows($rows)
    {
        $this->rows = $rows >= 0 ? $rows : 0;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset >= 0 ? $offset : 0;
    }

    public function setPage($page)
    {
        $this->page = $page >= 0 ? $page : 0;
    }

    public function getTotalRows()
    {
        return $this->totalRows;
    }

    public function getTotalPages()
    {
        return $this->totalPages;
    }

    public function getResultList()
    {
        return $this->resultList;
    }

}