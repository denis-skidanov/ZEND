<?php

class Sunny_DataMapper_CollectionAbstract implements Iterator, Countable, ArrayAccess
{
	/**
	 * Internal entries container
	 * 
	 * @var array
	 */
	protected $_data = array();
	
	/**
	 * Internal entries container pointer
	 * 
	 * @var mixed
	 */
	protected $_beyondLastEntry = false;
	
	/**
	 * Constructor
	 * 
	 * @param array $options OPTIONAL setup options
	 */
	public function __construct($options = null)
	{
		if (is_array($options)) {
			$this->setup($options);
		}
		
		$this->_beyondLastEntry = false;
	}
	
	/**
	 * Setup all options at once
	 * 
	 * @param array $options
	 */
	public function setup(array $options)
	{
		if (isset($options['data'])) {
			$this->setupData($options['data']);
		}
		
		return $this;
	}
	
	/**
	 * Setup collection data
	 * 
	 * @param array $data
	 */
	public function setupData(array $data)
	{
		$this->_data = $data;
		return $this;
	}
	
	/**
	 * Add entry to end of collection
	 * 
	 * @param mixed $entry
	 */
	public function addEntry($entry)
	{
		$this->_data[] = $entry;
		return $this;
	}
	
	/**
	 * Get array representation of collection
	 * 
	 * @return array
	 */
	public function toArray()
	{
		$array = array();
		foreach ($this->_data as $entry) {
			$array[] = $entry->toArray();
		}
		
		return $array;
	}
	
	/**
	 * Format date by format 'dayNum monthName fullYear yearSuffix'
	 * For entrie collection
	 * 
	 * @param string $fieldName
	 * @param array  $translatedMonths
	 * @param string $translatedYearSuffix
	 */
	public function formatDate($fieldName, $translatedMonths = array(), $translatedYearSuffix = null)
	{
		foreach ($this->_data as $entity) {
			$entity->formatDate($fieldName, $translatedMonths, $translatedYearSuffix);
		}
	}
	
	/**
	 * Get all entries identifiers in collection
	 * 
	 * @return array identifiers
	 */
	public function getIdentifiers()
	{
		$identifiers = array();
		foreach ($this->_data as $entry) {
			$identifiers[] = $entry->getIdentifier();
		}
		
		return $identifiers;
	}
	
	/**
	 * Return count of entries
	 * @see Countable
	 * 
	 * @return integer count
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Add or update entry by offset
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->_data[] = $value;
		} else {
			$this->_data[$offset] = $value;
		}
	}
	
	/**
	 * Check if entry by specified offset exists in collection
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}
	
	/**
	 * Delete entry from collection by offset
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}
	
	/**
	 * Get entry by offset
	 * @see ArrayAccess
	 * 
	 * @param mixed $offset
	 * @return mixed|null Returns NULL if entry not found
	 */
	public function offsetGet($offset)
	{
		return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
	}

	/**
	 * Reset internal pointer to first element of collection
	 * @see Iterator
	 */
    public function rewind()
    {
        reset($this->_data);
        $this->_beyondLastEntry = false;
    }

    /**
     * Return current pointed entry
     * @see Iterator
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Get identifier of current entry
     * @see Iterator
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Seek to next entry
     * @see Iterator
     */
    public function next()
    {
        $next = next($this->_data);
    	if (false === $next) {
    		$this->_beyondLastEntry = true;
    	}
    }

    /**
     * Check if pointed entry valid
     * @see Iterator
     */
    public function valid()
    {
        if (false !== $this->_beyondLastEntry || count($this->_data) == 0) {
        	return false;
        }
        
    	return true;
    }
}
