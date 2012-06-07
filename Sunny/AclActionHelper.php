<?php

/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

class Sunny_AclActionHelper extends Zend_Controller_Action_Helper_Abstract
{
	protected $_aclPlugin;
	
	public function __construct()
	{
		$this->getAclPlugin();
	}
	
	public function getAclPlugin()
	{
		if (null === $this->_aclPlugin) {
			require_once 'Zend/Controller/Front.php';
			$front = Zend_Controller_Front::getInstance();
			
			if (!$front->hasPlugin('Sunny_AclControllerPlugin')) {
				require_once 'Sunny/AclControllerPlugin.php';
				$front->registerPlugin(new Sunny_AclControllerPlugin());
			}
			
			$this->_aclPlugin = $front->getPlugin('Sunny_AclControllerPlugin');
		}
		
		return $this->_aclPlugin;
		
	}
	
	public function direct()
	{
		return $this->getAclPlugin();
	}
}