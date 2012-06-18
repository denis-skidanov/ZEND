<?php

class Sunny_DataMapper_DbTableAbstract extends Zend_Db_Table_Abstract
{
	/**
     * Support method for fetching rows (adding fetch mode).
     * @see Zend_Db_Table_Abstract::_fetch
     *
     * @param  Zend_Db_Table_Select $select  query options.
     * @return array An array containing the row results in FETCH_ mode.
     */
    protected function _fetch(Zend_Db_Table_Select $select, $fetchMode = Zend_Db::FETCH_ASSOC)
    {
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll($fetchMode);
        return $data;
    }
	
	/**
	 * Create Zend_Db_Table_Select object for fetch operations
	 * Based on offset mode
	 * 
     * @param string|array|Zend_Db_Table_Select $where   OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order   OPTIONAL An SQL ORDER clause.
     * @param int                               $count   OPTIONAL An SQL LIMIT count.
     * @param int                               $offset  OPTIONAL An SQL LIMIT offset.
     * @param array|string|Zend_Db_Expr         $columns OPTIONAL The columns to select from this table.
	 * @return Zend_Db_Table_Select
	 */
	public function createSelect($where = null, $order = null, $count = null, $offset = null, $columns = null)
	{
		if (!($where instanceof Zend_Db_Table_Select)) {
			$select = $this->select(true);
		
			if ($where !== null) {
				$this->_where($select, $where);
			}
		
			if ($order !== null) {
				$this->_order($select, $order);
			}
		
			if ($count !== null || $offset !== null) {
				$select->limit($count, $offset);
			}
		
			if ($columns !== null) {
        		$select->reset(Zend_Db_Select::COLUMNS);
				$select->columns($columns);
			}
		} else {
			$select = $where;
		}
		
		return $select;
	}
	
	/**
	 * Create Zend_Db_Table_Select object for fetch operations
	 * Based on page mode
	 * 
     * @param string|array|Zend_Db_Table_Select $where   OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order   OPTIONAL An SQL ORDER clause.
     * @param int                               $count   OPTIONAL An SQL LIMIT count.
     * @param int                               $page    OPTIONAL Page for SQL OFFSET AND LIMIT
     * @param array|string|Zend_Db_Expr         $columns OPTIONAL The columns to select from this table.
	 * @return Zend_Db_Table_Select
	 */
	public function createSelectPage($where = null, $order = null, $count = null, $page = null, $columns = null)
	{
		$offset = null;
		if (null !== $count && null !== $page) {
			$offset = $page * $count - $count;
		}
		
		return $this->createSelect($where, $order, $count, $offset, $columns);
	}
	
	/**
	* Override default _setupTableName method
	*
	* (non-PHPdoc)
	* @see Zend_Db_Table_Abstract::_setupTableName()
	*/
	protected function _setupTableName()
	{
		if (!$this->_name) {
			$this->_name = $this->_formatInflectedTableName(get_class($this));
		}
	
		parent::_setupTableName();
	}
	
	/**
	 * Convert child class name to database table name
	 *
	 * @param string $name
	 * @return string
	 */
	protected function _formatInflectedTableName($name)
	{
		$name = explode('_', $name);
		$name = end($name);
	
		$filter = new Zend_Filter_Word_CamelCaseToUnderscore();
		return strtolower($filter->filter($name));
	}
	
	/**
	 * Override fetch all method - add columns parameter
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::fetchAll()
	 * 
	 * @return array Result rowset
	 */
	public function fetchAll($where = null, $order = null, $count = null, $offset = null, $columns = null)
	{
		$select = $this->createSelect($where, $order, $count, $offset, $columns);
		$rows =  $this->_fetch($select);
		
		return $rows;
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
		$offset = null;
		if (null !== $count && null !== $page) {
			$offset = $page * $count - $count;
		}
	
		return $this->fetchAll($where, $order, $count, $offset, $columns);
	}
	
	/**
	 * Override fetch row method - add columns parameter
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::fetchRow()
	 * 
	 * @return null|array Result row or null if not found
	 */
	public function fetchRow($where = null, $order = null, $columns = null)
	{
		$select = $this->createSelect($where, $order, 1, null, $columns);
		$rows = $this->_fetch($select);
		
		if (count($rows) == 0) {
			return null;
		}
		
		return $rows[0];
	}
	
	/**
	 * Retrieve count of rows in table
	 * 
     * @param string|array|Zend_Db_Select|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause
	 * @return integer Count of rows
	 */
	public function fetchCount($where = null)
	{
        // Prepare statement
        $pk      = current($this->info(self::PRIMARY));
		$columns = new Zend_Db_Expr('COUNT(' . $this->quoteIdentifier($pk) . ')');
		$select  = $this->createSelect($where, null, null, null, $columns);
		
		// Fetch one
		$rows = $this->_fetch($select, Zend_Db::FETCH_COLUMN);
		if (count($rows) == 0) {
			return 0;
		}
		
		return $rows[0];
	}
	
	/**
	 * Find row by primary key value
	 * 
	 * @param  string|number             $id
     * @param  array|string|Zend_Db_Expr $columns OPTIONAL The columns to select from this table.
	 * @return null|array Result row or null if not found
	 */
	public function findByPrimaryKey($id, $columns = null)
	{
        $pk     = current($this->info(self::PRIMARY));
		$where  = $this->quoteInto($this->quoteIdentifier($pk) . ' = ?', $id);
		$select = $this->createSelect($where, null, 1, null, $columns);
		
		return $this->fetchRow($select);
	}
	
	/**
	* Find rowset by primary keys array values
	*
	* @param  array                     $idArray
	* @param  array|string|Zend_Db_Expr $columns OPTIONAL The columns to select from this table.
	* @return null|array Result row or null if not found
	*/
	public function findByPrimaryKeysArray(array $idArray, $where = null, $columns = null)
	{
		if (empty($idArray)) {
			return array();
		}

		$select = $this->createSelect($where, null, null, null, $columns);
		
		$idArray = array_values($idArray);
		$idArray = array_unique($idArray);
        
		$pk        = current($this->info(self::PRIMARY));
		$condition = $this->quoteIdentifier($pk) . ' = ?';
		$where     = array();
		foreach ($idArray as $id) {
			$where[] = $this->quoteInto($condition . ' = ?', $id);
		}
		
		$select->where(implode(' ' . Zend_Db_Table_Select::SQL_OR . ' ', $where));
		return $this->fetchAll($select);
	}
	
	/**
	 * Proxy to adapter quote into method
	 * @see Zend_Db_Adapter_Abstract
	 * 
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
	 */
	public function quoteInto($text, $value, $type = null, $count = null)
	{
		return $this->getAdapter()->quoteInto($text, $value, $type = null, $count = null);
	}
	
	/**
	 * Proxy to adapter quote identifier method
	 * @see Zend_Db_Adapter_Abstract
	 * 
     * @param string|array|Zend_Db_Expr $ident The identifier.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier.
	 */
	public function quoteIdentifier($ident, $auto = false)
	{
		return $this->getAdapter()->quoteIdentifier($ident, $auto);
	}
	
	public function delete($id)
	{
		$where = $this->quoteInto($this->quoteIdentifier(current($this->info(self::PRIMARY))), $id);
		return parent::delete($where);
	}
}