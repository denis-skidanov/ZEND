<?php

class Sunny_DataMapper_MapperAbstract
{
	/**
	 * Internal db table object container
	 * 
	 * @var Zend_Db_Table_Abstract
	 */
	protected $_dbTable;
	
	/**
	 * Internal cache object container
	 * 
	 * @var Zend_Cache_Core
	 */
	protected static $_cache;
 
    /**
     * Format entity class name from mapper name
     * 
     * @param string $name
     * @throws Exception
     */
	protected function _formatEntityName($name)
    {
       	$parts = explode('_', $name);
       	$parts[count($parts) - 2] = 'Entity';
       	return implode('_', $parts);
    }
    
    /**
    * Format collection class name from mapper name
    *
    * @param string $name
    * @throws Exception
    */
    protected function _formatCollectionName($name)
    {
    	$parts = explode('_', $name);
    	$parts[count($parts) - 2] = 'Collection';
    	return implode('_', $parts);
    }
    
    /**
     * Format DbTable class name from mapper name
     * 
     * @param string $name
     * @throws Exception
     */
    protected function _formatDbTableName($name)
    {
       	$parts = explode('_', $name);
       	$parts[count($parts) - 2] = 'DbTable';
       	return implode('_', $parts);
    }
    
    /**
     * Gets database adapter from current table object
     */
    protected function _getDbTableAdapter()
    {
    	return $this->getDbTable()->getAdapter();
    }
    
    /**
     * Convert rowset to collection object
     * 
     * @param array $rowset Result array of rows from db
     * @return Sunny_DataMapper_CollectionAbstract
     */
    protected function _rowsetToCollection(array $rowset = array())
    {
    	// Store every row to a new created model and store it to result array
    	$collection = array();
    	foreach ($rowset as $row) {
    		$collection[] = $this->createEntity($row);
    	}
    	
    	// Return rows
    	return $this->createCollection($collection);    	 
    }
    
    /**
     * Convert single row to entity object
     * 
     * @param  array $row Db result row
     * @return Sunny_DataMapper_EntityAbstract
     */
    protected function _rowToEntity($row = null)
    {
    	if (null == $row) {
    		return null;
    	}
    	
    	// Store row data to model and return it
    	return $this->createEntity($row);
    }
    
    /**
     * Setup internal cache container
     * 
     * @param Zend_Cache_Core $cache
     */
    public static function setupModelCache(Zend_Cache_Core $cache)
    {
    	self::$_cache = $cache;
    }
    
    /**
     * Magic method: uses when retrieving cached model
     * 
     * @param  string $name
     * @param  array  $arguments
     * @throws Exception
     * @return mixed
     */
    public function __call($name, $arguments)
    {
    	$method = strtolower(substr($name, 6, 1)) . substr($name, 7);
    	//$method = lcfirst(substr($name, 6));
    	if ('cached' == substr($name, 0, 6) && method_exists($this, $method) && null !== self::$_cache) {
    		$class = get_class($this);
    		$id    = $class . '_' . $method . '_' . md5(serialize($arguments));
    		$tags  = array($class, $method);
    		 
    		if (!($result = self::$_cache->load($id))) {
    			$result = call_user_func_array(array($this, $method), $arguments);
    			self::$_cache->save($result, $id, $tags);
    		}
    		
    		return $result;
    	}
    	
    	if (null === self::$_cache) {
    		throw new Exception("Cache object not provided", 500);
    	}
    	
    	if (!method_exists($this, $method)) {    		
    		throw new Exception("Undefined method " . get_class($this) . '::' . $method, 500);
    	}
    }
    
    /**
     * Set db table object
     * 
     * @param string|Zend_Db_Table_Abstract $dbTable
     * @throws Exception
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * Get db table object
     * If not set, create default
     * 
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable($this->_formatDbTableName(get_class($this)));
        }
        
        return $this->_dbTable;
    }
    
    /**
     * Quote identifier for use in custom queries
     * 
     * @see Zend_Db_Adapter_Abstract for more information about arguments
     * @param mixed $ident
     * @param boolean $auto
     * 
     * @return string
     */
    public function quoteIdentifier($ident, $auto = false)
    {
    	return $this->getDbTable()->quoteIdentifier($ident, $auto);
    }
    
    /**
     * Quote identifier for use in custom queries
     * 
     * @see Zend_Db_Adapter_Abstract for more information about arguments
     * @param mixed $ident
     * @param boolean $auto
     * 
     * @return string
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
    	return $this->getDbTable()->quoteInto($text, $value, $type, $count);
    }
    
    /**
     * Create new entity
     * 
     * @param  array $data initial content data
     * @return Application_Model_Abstract object
     */
    public function createEntity(array $data = array())
    {
    	$columns = $this->getDbTable()->info(Zend_Db_Table_Abstract::COLS);
    	$columns = array_fill_keys(array_values($columns), null);
    	
    	// Filter data
    	$data = array_intersect_key($data, $columns);
    	$data = array_merge($columns, $data);
    	
    	$options = array(
    		'data'       => $data,
    		'identifier' => $data[current($this->getDbTable()->info(Zend_Db_Table_Abstract::PRIMARY))]
    	);
    	
    	$entityName = $this->_formatEntityName(get_class($this));
    	return new $entityName($options);
    }
    
    /**
     * Create new collection
     * 
     * @param array $data entries array
     * @return object instance of Sunny_DataMapper_CollectionAbstract
     */
    public function createCollection(array $data = array())
    {
    	$collectionName = $this->_formatCollectionName(get_class($this));
    	return new $collectionName(array('data' => $data));
    }
	
	/**
	 * Find row by primary key
	 * 
	 * @param number                    $id      Primary key value
	 * @param string|array|Zend_Db_Expr $columns Columns for result
	 * @return Sunny_DataMapper_EntityAbstract
	 */
	public function findEntity($id, $columns = null)
	{
		$row = current($this->getDbTable()->find($id, $columns));
		return $this->_rowToEntity($row);
	}
	
	/**
	 * Find rows by primary key values
	 * 
	 * @param array                     $idArray Array of primary key values
	 * @param string|array|Zend_Db_Expr $where   OPTIONAL Sql where clause
	 * @param string|array|Zend_Db_Expr $columns OPTIONAL Sql columns clause
	 */
	public function findCollection(array $idArray, $columns = null)
	{
		$rowSet = $this->getDbTable()->find($idArray, $columns);
		return $this->_rowsetToCollection($rowSet);
	}
	
	/**
	 * Save entity to db
	 * 
	 * @param Sunny_DataMapper_EntityAbstract $entity
	 * @return mixed Return primary key value if new row inserted
	 */
	public function saveEntity($entity)
	{
		$e  = $entity->toArray();
		$id = $entity->getId();
		
		// Cleanup data
		
		// if nothing to write - return
		if (empty($e)) {
			return false;
		}
		
		if (isset($e['date_created'])) {
			$e['date_created'] = (int) $e['date_created'];
			if (empty($e['date_created'])) {
				$e['date_created'] = time();
			}
		}
		
		if (array_key_exists('date_modified', $e)) {
			$e['date_modified'] = time();
		}
		
		$data = array();
		foreach ($e as $key => $value) {
			if (null !== $value) {
				$data[$key] = $value;
			}
		}
		
		if (empty($id)) {
			unset($data['id']);
			return $this->getDbTable()->insert($data);
		} else {
			$this->getDbTable()->update($data, $id);
			return $id;
		}
	}
	
	/**
	 * Save collection at once
	 * 
	 * @param Sunny_DataMapper_CollectionAbstract $collection
	 */
	public function saveCollection(Sunny_DataMapper_CollectionAbstract $collection)
	{
		$success = array();
		foreach ($collection as $entity) {
			$success[$entity->getIdentifier()] = $this->saveEntity($entity);
		}
		
		return $success;
	}
	
	/**
	 * Delete single entity
	 * 
	 * @param Sunny_DataMapper_EntityAbstract $entity
	 */
	public function deleteEntity(Sunny_DataMapper_EntityAbstract $entity)
	{
		$id = $entity->getIdentifier();
		if (!$id) {
			return false;
		}
		
		return $this->getDbTable()->delete($entity->getIdentifier());
	}
	
	/**
	 * Delete collection of entities
	 * 
	 * @param Sunny_DataMapper_CollectionAbstract $collection
	 */
	public function deleteCollection(Sunny_DataMapper_CollectionAbstract $collection)
	{
		$this->getDbTable()->delete($collection->getIdentifiers());
	}
    
	/**
	 * Fetches single row
	 * @see Zend_Db_Table for more information about arguments
	 * 
	 * @param mixed $where
	 * @param mixed $order
	 * @param Application_Model_Abstract $model
	 * @throws Exception
	 * @return mixed
	 */
	public function fetchRow($where = null, $order = null)
	{
		$result = $this->getDbTable()->fetchRow($where, $order);
		return $this->_rowToEntity($result);
	}
	
	/**
	 * Fetches many rows
	 * @see Zend_Db_Table for more information about arguments
	 * 
	 * @param mixed $where
	 * @param mixed $order
	 * @param integer $count
	 * @param integer $offset
	 * @return Sunny_DataMapper_CollectionAbstract
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		$rowSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
		return $this->_rowsetToCollection($rowSet);
	}
	
	/**
	 * Fetches row count from current table
	 * @see Sunny_DataMapper_DbTableAbstract for more information about arguments
	 * 
	 * @param mixed $where
	 * @return integer count of rows
	 */
	public function fetchCount($where = null)
	{
		return $this->getDbTable()->fetchCount($where);
	}
	
	/**
	 * Fetches rowset by page number instead of offset
	 * @see Sunny_DataMapper_MapperAbstract::fetchAll()
	 * @see Zend_Db_Table_Abstract::fetchAll()
	 * 
	 * @param mixed $where
	 * @param mixed $order
	 * @param integer $count
	 * @param integer $page
	 * @return Sunny_DataMapper_CollectionAbstract
	 */
	public function fetchPage($where = null, $order = null, $count = null, $page = null, $columns = null)
	{
		$rowSet = $this->getDbTable()->fetchPage($where, $order, $count, $page, $columns);
		return $this->_rowsetToCollection($rowSet);
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @return NULL
	 */
	public function fetchTree($where = null, $columns = null, $ordering = null)
	{
		$rowSet = $this->getDbTable()->fetchTree($where, $columns, $ordering);
		$name = $this->getDbTable()->info(Zend_Db_Table_Abstract::NAME);
		$pk = current($this->getDbTable()->info(Zend_Db_Table_Abstract::PRIMARY));
		
		if(empty($rowSet)){
			return null;
		} 
		
		return $this->_generateTree($rowSet, $pk, $name);
	}

	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $rowSet
	 * @param unknown_type $pk
	 * @param unknown_type $name
	 * @param unknown_type $pkValue
	 */
	protected function _generateTree($rowSet, $pk, $name, $pkValue = 0)
	{
		$collection = $this->createCollection();
		
		foreach ($rowSet as $row) {
			if ($row[$name . '_' . $pk] == $pkValue) {
				$entity = $this->_rowToEntity($row);
				$entity->setExtendChilds($this->_generateTree($rowSet, $pk, $name, $row[$pk]));
				$collection->addEntry($entity);
			}
		}	
		return $collection;
	} 
}
