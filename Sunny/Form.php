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
	
	public function collectionToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
		
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->title;
				if ($entity->getExtendChilds()->count() > 0) {
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