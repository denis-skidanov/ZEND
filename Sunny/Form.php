<?php

/*
 * 1. __construct()
 * 2. setOptions()
 *    2.1 call extending methods if exists
 * 3. init()
 */

require_once 'Zend/Form.php';

class Sunny_Form extends Zend_Form
{
	public function __construct($options = null)
	{
		$this->setCompositeDecorators();
		parent::__construct();
	}
	
	/**
	 * Generates list of element "select" like tree in Zend_Form
	 * Generate tree like named options list for "Multi" form elements
	 * 
	 * @param  Sunny_DataMapper_CollectionAbstract $collection  Input collection
	 * @param  array                               $exclude     Array of excluded identifiers
	 * @param  array                               $result      Previous result or pre defined options
	 * @param  array                               $level       Tree deep level
	 * @return array  Result options
	 */
	public function collectionToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
		
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->title;
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	public function onecEmploeesCollectionToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
	
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->sname . ' ' . $entity->name . ' ' . $entity->pname;
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	
	
	public function createAssocMultioptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
		
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->alias] = $titleOffset . ' ' . $entity->title;
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	public function setElementMultiOptions($name, array $options = array())
	{
		if (is_string($name)) {
			$element = $this->getElement($name);
		}
		
		if (!$element instanceof Zend_Form_Element) {
			return $this;
		}
		
		$element->setMultiOptions($options);
	}
	
	public function setCompositeDecorators()
	{
		$this->addElementPrefixPath('Sunny_Form_Decorator', 'Sunny/Form/Decorator/', 'decorator');
		$this->setElementDecorators(array('CompositeElementDiv'));
				
		$this->addDisplayGroupPrefixPath('Sunny_Form_Decorator', 'Sunny/Form/Decorator/', 'decorator');
		$this->setDisplayGroupDecorators(array('CompositeGroupDiv'));
				
		$this->addPrefixPath('Sunny_Form_Decorator', 'Sunny/Form/Decorator/', 'decorator');
		$this->setDecorators(array('CompositeFormDiv'));
	}
}