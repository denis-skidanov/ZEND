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
     * Format model class name from mapper name
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
     * Create new empty domain model
     * 
     * @return Application_Model_Abstract object
     */
    public function create(array $data = array())
    {
    	$modelName = $this->_formatEntityName(get_class($this));
    	$model = new $modelName(array('colNames' => $this->getDbTable()->info(Zend_Db_Table_Abstract::COLS)));
    	
    	if (!empty($data)) {
    		$model->setupColData($data);
    	}
    	
    	return $model;
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
		return $this->create($result->toArray());
	}
	
	/**
	 * Fetches many rows
	 * @see Zend_Db_Table for more information about arguments
	 * 
	 * @param mixed $where
	 * @param mixed $order
	 * @param integer $count
	 * @param integer $offset
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		// Fetches rows from database
		$rowSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
		
		// Store every row to a new created model and store it to result array
		$entries = array();
		foreach ($rowSet as $row) {
			$entries[] = $this->create($row->toArray());
		}
		
		// Return array of rows
		return $entries;
	}
}
