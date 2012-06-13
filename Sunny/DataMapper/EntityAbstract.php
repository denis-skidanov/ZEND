<?php

abstract class Sunny_DataMapper_EntityAbstract implements ArrayAccess//, JsonSerializable
{
	/**
	 * Internal data container
	 * 
	 * @var array
	 */
	protected $_columns = array();
	
	/**
	 * Ignore columns which not exists in model
	 * 
	 * @var boolean
	 */
	protected $_ignoreUndefinedNames = false;
	
	/**
	 * Convert camel case outer names to internal
	 * 
	 * @param unknown_type $value
	 */
	protected function _camelCaseToUnderscoreLowerCase($value)
	{
		require_once 'Zend/Filter.php';
		return strtolower(Zend_Filter::filterStatic($value, 'Word_CamelCaseToUnderscore'));
	}
	
	/**
	 * Constructor
	 * 
	 * @param array $options Setup options for row model (field names)
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setup($options);
		}
	}
	
	/**
	 * Set new value for field (class variable acess type)
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws Exception
	 */
	public function __set($name, $value = null)
	{
		$name = $this->_camelCaseToUnderscoreLowerCase($name);
		
		if (!array_key_exists($name, $this->_columns)) {
			if (!$this->_ignoreUndefinedNames) {
				// If field name not found - error
				throw new Exception("Invalid set property name '$name'", 500);
			}
			
			return;
		}
		
		$this->_columns[$name] = $value;
		return $this;
	}
	
	/**
	 * Get specified field value (class variable acess type)
	 * 
	 * @param string $name
	 * @throws Exception
	 * @return mixed
	 */
	public function __get($name)
	{
		$name = $this->_camelCaseToUnderscoreLowerCase($name);
		
		if (!array_key_exists($name, $this->_columns)) {
			if (!$this->_ignoreUndefinedNames) {
				// If field name not found - error
				throw new Exception("Invalid get property name '$name'", 500);				
			}
			
			return;
		}		
		
		return $this->_columns[$name];
	}
	
	/**
	 * Get or set field value (class method acess type)
	 * 
	 * @param string $name
	 * @param array $arguments (used only first element of array)
	 * @throws Exception
	 */
	public function __call($name, $arguments)
	{
		if ('setup' == substr(strtolower($name), 0, 5)) {
			// If undefined setup method - prevent setting illegal column
			throw new Exception("Call to undefined method " . __METHOD__, 500);
		}
		
		$prefix = '__' . substr(strtolower($name), 0, 3);		
		switch ($prefix) {
			case 'set':
			case 'get':
				array_unshift($arguments, substr($name, 3));
				return call_user_func_array(array($this, $prefix), $arguments);
			default:
				throw new Exception("Invalid property or method name '$name'", 500);
		}
	}
	
	/**
	 * Setup options
	 * 
	 * @param array $options
	 */
	public function setup(array $options)
	{
		// Setup ignore or not undefined names
		if (isset($options['ignoreUndefinedNames'])) {
			$this->_ignoreUndefinedNames = (bool) $options['ignoreUndefinedNames'];
		}
		
		// Setup columns names
		if (isset($options['colNames']) && is_array($options['colNames'])) {
			$this->setupColNames($options['colNames']);
		}
				
		return $this;		
	}
	
	/**
	 * Setup columns names
	 * 
	 * @param array $columns
	 */
	public function setupColNames(array $colNames = array())
	{
		// Normalize columns list
		$colNames = array_values($colNames);
		
		// Convert to internal data array
		$this->_columns = array_fill_keys($colNames, null);
		
		return $this;
	}
	
	/**
	 * Setup columns data at once
	 * 
	 * @param array $colData
	 */
	public function setupColData(array $colData = array())
	{
		foreach ($colData as $name => $value) {
			$this->__set($name, $value);
		}
		
		return $this;
	}
	
	/**
	 * Returns array representation of model
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return (array) $this->_columns;
	}
	
	/**
	 * Check if param exists
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 */
	public function offsetExists($offset)
	{
		return isset($this->_columns[$offset]);
	}
	
	/**
	 * Get parameter value by offset name
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}
	
	/**
	 * Set parameter value by offset name
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}
	
	/**
	 * Unset now not available
	 * 
	 * @param mixed $offset
	 * @throws Exception
	 */
	public function offsetUnset($offset)
	{
		throw new Exception("Entity parameter unset not available", 500);
	}
	
	public function jsonSerialize()
	{
		return $this->_columns;
	}
}