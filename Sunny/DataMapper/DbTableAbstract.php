<?php

class Sunny_DataMapper_DbTableAbstract extends Zend_Db_Table_Abstract
{
	/**
     * Support method for fetching rows (adding fetch mode).
     * @see Zend_Db_Table_Abstract::_fetch
     *
     * @param  Zend_Db_Table_Select $select  query options.
     * @return array An array containing the row results in FETCH_ASSOC mode.
     */
    protected function _fetch(Zend_Db_Table_Select $select, $fetchMode = Zend_Db::FETCH_ASSOC)
    {
        $stmt = $this->_db->query($select);
        $data = $stmt->fetchAll($fetchMode);
        return $data;
    }
	
	/**
	 * Create Zend_Db_Table_Select object for fetch operations
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
	
	//public function _fetchCol(){}
	//public function _fetchOne(){}
	
	/**
	 * Retrieve count of rows in table
	 * 
     * @param string|array|Zend_Db_Select|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause
	 * @return integer Count of rows
	 */
	public function fetchCount($where = null)
	{
        // Prepare statement
		$columns = new Zend_Db_Expr('COUNT(' . $this->quoteIdentifier($this->_primary) . ')');
		$select  = $this->createSelect($where, null, null, null, $columns);
		
		// Fetch one
		$rows = $this->_fetch($select, Zend_Db::FETCH_COLUMN);
		if (count($rows) == 0) {
			return 0;
		}
		
		return $rows[0];
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
}