<?php

abstract class Sunny_DataMapper_MapperAbstract
{
	/**
	 * Internal db table object container
	 * 
	 * @var Zend_Db_Table_Abstract
	 */
	protected $_dbTable;
 
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
    	return $this->getDbTable()->getAdapter()->quoteIdentifier($ident, $auto);
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
    	return $this->getDbTable()->getAdapter()->quoteInto($text, $value, $type, $count);
    }
    
    /**
     * Create new entity
     * 
     * @param  array $data initial content data
     * @return Application_Model_Abstract object
     */
    public function createEntity(array $data)
    {
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
     * Update or insert model data to database
     * Return number of affected rows on success or false otherwise
     * 
     * @param Application_Model_Abstract $model
     * @throws Exception
     * @return mixed
     */
    public function save($model)
	{
		// Prepare data
		$data = $model->toArray();
		$id = $model->getId();
		
		if (empty($id)) {
			// If id not set - insert new
			unset($data['id']);
			// Zend_Db_Table insert return primary key value unlike as adapters insert method
			// So we check if null
			$return = $this->getDbTable()->insert($data);
			return !is_null($return);
		} else {
			// Else update existing
			$return = $this->getDbTable()->update($data, array('id = ?' => $id));
			return (bool) $return;
		}
	}
	
	/**
	* Delete records from db
	*
	* @see Zend_Db_Table for mode information about argument
	* @param  mixed $where
	* @return number of affected rows
	*/
	public function delete($where)
	{
		return $this->getDbTable()->delete($where);
	}
	
	/**
	 * Find row by primary key(s)
	 * 
	 * @param integer $id
	 * @param Application_Model_Abstract $model
	 * @throws Exception
	 * @return mixed
	 */
	public function find($id)
	{
		// Fetch from database
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			// Error - empty result
			return false;
		}
		
		// Store date to model and return it
		$row = $result->current();		
		return $this->create($row->toArray());
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
		// Fetch row from database
		$result = $this->getDbTable()->fetchRow($where, $order);
		if (null == $result) {
			// Error - empty result
			return false;
		}
		
		// Store row data to model and return it
		return $this->createEntity($result->toArray());
	}
	
	/**
	 * TODO: change layer
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
		// Fetches rows from database
		$rowSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
		
		// Store every row to a new created model and store it to result array
		$collection = array();
		foreach ($rowSet as $row) {
			$collection[] = $this->createEntity($row->toArray());
		}
		
		// Return rows
		return $this->createCollection($collection);
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
	public function fetchPage($where = null, $order = null, $count = null, $page = null)
	{
		$offset = null;
		if (null !== $count && null !== $page) {
			$offset = $page * $count - $count;
		}
		
		return $this->fetchAll($where, $order, $count, $offset);
	}
}
