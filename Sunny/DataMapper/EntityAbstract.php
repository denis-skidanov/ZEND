<?php

class Sunny_DataMapper_EntityAbstract
{
	/**
	 * Internal data container
	 * 
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Extensions container
	 * 
	 * @var array
	 */
	protected $_extentions = array();
	
	/**
	 * Internal entry identifier
	 * 
	 * @var string|integer
	 */
	protected $_identifier;
	
	/**
	 * Ignore columns which not exists in model
	 * 
	 * @var boolean
	 */
	protected $_ignoreUndefinedNames = false;
	
	/**
	 * Convert camel case outer names to internal
	 * 
	 * @param string $value
	 */
	protected function _camelCaseToUnderscoreLowerCase($value)
	{
		require_once 'Zend/Filter.php';
		return strtolower(Zend_Filter::filterStatic($value, 'Word_CamelCaseToUnderscore'));
	}
	
	
	/**
	 * Setting an setIgnoreUndefinedNames value
	 * @param bool $value
	 */
	public function setIgnoreUndefinedNames($value)
	{
		$this->_ignoreUndefinedNames = (bool) $value;
		return $this;
	}
	
	
	/**
	* Returns an setIgnoreUndefinedNames value
	* @param bool $value
	*/
	public function getIgnoreUndefinedNames()
	{
		return $this->_ignoreUndefinedNames;
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
		
		if (!array_key_exists($name, $this->_data) && !$this->_ignoreUndefinedNames) {
			// If field name not found - error
			throw new Exception("Invalid set property name '$name'", 500);			
		}
		
		$this->_data[$name] = $value;
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
		
		if (!array_key_exists($name, $this->_data)) {
			if (!$this->_ignoreUndefinedNames) {
				// If field name not found - error
				throw new Exception("Invalid get property name '$name'", 500);				
			}
			
			return;
		}		
		
		return $this->_data[$name];
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
		
		$prefixExtend = substr($name, 0, 9);		
		switch ($prefixExtend) {
			case 'setExtend':
			case 'getExtend':
				array_unshift($arguments, substr($name, 9));
				return call_user_func_array(array($this, $prefixExtend), $arguments);
			//default:
				//throw new Exception("Invalid property or method name '$name'", 500);
		}
		
		$prefix = '__' . substr(strtolower($name), 0, 3);
		switch ($prefix) {
			case '__set':
			case '__get':
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
		if (isset($options['data'])) {
			$this->setupData($options['data']);
		}
		
		// Setup identifier value
		if (isset($options['identifier'])) {
			$this->setupIdentifier($options['identifier']);
		}
				
		return $this;		
	}
	
	/**
	 * Setup columns data at once
	 * 
	 * @param array $colData
	 */
	public function setupData(array $data)
	{
		$this->_data = $data;
		return $this;
	}
	
	/**
	 * Setup entry identifier
	 * 
	 * @param string|integer $identifier
	 */
	public function setupIdentifier($identifier)
	{
		$this->_identifier = $identifier;
		return $this;
	}
	
	/**
	 * Get entry identifier value
	 * 
	 * @return string|integer identifier
	 */
	public function getIdentifier()
	{
		return $this->_identifier;
	}
	
	public function setExtend($name, $data)
	{
		if (!$data instanceof Sunny_DataMapper_CollectionAbstract && 
		    !$data instanceof Sunny_DataMapper_EntityAbstract) {
			if (!$this->_ignoreUndefinedNames) {
				throw new Exception('Invalid extention data provided', 500);
			}

			return $this;
		}
		
		$this->_extentions[$name] = $data;
		return $this;
		
	}
	
	public function setExtendArray($name, $data)
	{
		if (!is_array($data)) {
			throw new Exception('Invalid extention data provided', 500);
			return $this;
		}
	
		$this->_extentions[$name] = $data;
		return $this;
	
	}
	
	public function getExtend($name)
	{
		return $this->_extentions[$name];
	}
	
	/**
	 * Returns array representation of model
	 * 
	 * @return array
	 */
	public function toArray()
	{
		$return = $this->_data;
		
		foreach ($this->_extentions as $name => $extension) {
			$return['extend' . ucfirst($name)] = $extension->toArray();
		}
		
		return $return;
	}
	
	/**
	 * Format date by format 'dayNum monthName fullYear yearSuffix'
	 * Extensions included in processing
	 * 
	 * @param string $fieldName
	 * @param array  $translatedMonths
	 * @param string $translatedYearSuffix
	 */
	public function formatDate($fieldName, $translatedMonths = array(), $translatedYearSuffix = null)
	{
		if (array_key_exists($fieldName, $this->_data)) {
			$formattedDate = date('d', $this->_data[$fieldName]);
			
			if (!empty($translatedMonths) && count($translatedMonths) == 12) {
				$formattedDate .= ' ' . $translatedMonths[(int) date('m', $this->_data[$fieldName])];
			} else {
				$formattedDate .= ' ' . date('m', $this->_data[$fieldName]);
			}
			
			$formattedDate .= ' ' . date('Y', $this->_data[$fieldName]);
			
			if (null !== $translatedYearSuffix) {
				$formattedDate .= ' ' . trim($translatedYearSuffix);
			}
			
			$this->_data[$fieldName] = $formattedDate;
		}
		
		
		foreach ($this->_extentions as $extension) {
			$extension->formatDate($fieldName, $translatedMonths, $translatedYearSuffix);
		}
	}
}