<?php

abstract class Sunny_DataMapper_DbTableAbstract extends Zend_Db_Table_Abstract
{
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
	 * Get total rows count
	 * 
	 * @param string|array|Zend_Db_Table_Select $where OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
	 * @return integer
	 */
	public function fetchCount($where = null)
	{
        if (!($where instanceof Zend_Db_Table_Select)) {
            $select = $this->select(true);

            if ($where !== null) {
                $this->_where($select, $where);
            }
        } else {
            $select = $where;
        }
        
        // Reset parts
        $select->reset(Zend_Db_Table_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Table_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Table_Select::COLUMNS);
        
        $select->columns(new Zend_Db_Expr(
        	'COUNT(' . $this->getAdapter()->quoteIdentifier(implode(',', $this->info(self::PRIMARY))) . ')'
        ));
        
        return $this->getAdapter()->fetchOne($select);
	}
}