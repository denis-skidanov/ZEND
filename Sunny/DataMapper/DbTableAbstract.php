<?php

class Sunny_DataMapper_DbTableAbstract extends Zend_Db_Table_Abstract
{
	/**
	 * Custom query to database
	 * 
	 * @param string $sql 
	 */
	public function query($sql)
	{
		$this->getAdapter()->query($sql);
	}
	
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
		//echo $select;
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
	 * Fetch tree when table has chain keys
	 * 
	 * @param mixed $where
	 * @param mixed $columns
	 * @return array
	 */
	public function fetchTree($where = null, $columns = null, $ordering = null)
	{
		$name 		= $this->info(self::NAME);
		$pk 		= current($this->info(self::PRIMARY));
		$cols 		= $this->info(self::COLS);
		$tree_col 	= $name . '_' . $pk;
		
		if (!in_array($tree_col, $cols)) {
			return array();
		}
		
		$select = $this->createSelect($where, $ordering, null, null, $columns);
		return  $this->_fetch($select);
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
	
	/**
	* Find row(s)
	*
	* @param  int|array                 $idArray Primary key value(s)
	* @param  array|string|Zend_Db_Expr $columns OPTIONAL The columns to select from this table.
	* @return null|array Result row or null if not found
	*/
	public function find($idArray, $columns = null)
	{
		$idArray = (array) $idArray;
		$idArray = array_unique($idArray);
		
		if (empty($idArray)) {
			return array();
		}
		
		$where = array();
		foreach ($idArray as $id) {
			$where[] = $this->quoteInto($this->quoteIdentifier(current($this->info(self::PRIMARY))) . ' = ?', $id);
		}
		
		$where = implode(' ' . Zend_Db_Select::SQL_OR . ' ', $where);
		$select = $this->createSelect($where, null, count($idArray), null, $columns);
	
		return $this->_fetch($select);
	}
	
	/**
	 * Update record by primary key
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Db_Table_Abstract::update()
	 * 
	 * @param  array $data Data for update
	 * @param  int   $id   Primary key value
	 * @return int   The number of affected rows
	 */
	public function update($data, $id)
	{
		$where = $this->quoteInto($this->quoteIdentifier(current($this->info(self::PRIMARY))) . ' = ?', $id);
		return parent::update($data, $where);
	}
	
	/**
	 * Allow deleting multiple rows by his primary key values
	 * 
	 * (non-PHPdoc)
	 * @see    Zend_Db_Table_Abstract::delete()
	 * 
	 * @param  int|array $idArray Primary key value(s)
	 * @return int       The number of rows deleted.
	 */
	public function delete($idArray)
	{
		$idArray = (array) $idArray;
		$idArray = array_unique($idArray);
		
		if (empty($idArray)) {
			return;
		}
		
		$where = array();
		foreach ($idArray as $id) {
			$where[] = $this->quoteInto($this->quoteIdentifier(current($this->info(self::PRIMARY))) . ' = ?', $id);
		}
		
		return parent::delete(implode(' ' . Zend_Db_Select::SQL_OR . ' ', $where));
	}
	
	public function deleteWhere($where)
	{
		return parent::delete($where);
	}
}