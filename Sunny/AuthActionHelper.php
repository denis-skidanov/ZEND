<?php

require_once 'Zend/Controller/Action/Helper/Abstract.php';

class Sunny_AuthActionHelper extends Zend_Controller_Action_Helper_Abstract
{
	protected $_authPlugin;
	
	public function __construct()
	{
		$this->getAuthPlugin();
	}
	
	public function getAuthPlugin()
	{
		if (null === $this->_authPlugin) {
			require_once 'Zend/Controller/Front.php';
			$front = Zend_Controller_Front::getInstance();
			
			if (!$front->hasPlugin('Sunny_AuthControllerPlugin')) {
				require_once 'Sunny/AuthControllerPlugin.php';
				$front->registerPlugin(new Sunny_AuthControllerPlugin());
			}
			
			$this->_authPlugin = $front->getPlugin('Sunny_AuthControllerPlugin');
		}
		
		return $this->_authPlugin;
		
	}
	
	public function direct()
	{
		return $this->getAuthPlugin();
	}
}