<?php

class Sunny_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
	const DEFAULT_ROLE = 'guest';
	const ORIGINAL_REQUEST = 'original-request';
	
	/**
	 * Plugin configuration
	 * 
	 * @var array
	 */
	protected $_options = array();
	
	/**
	 * Denied page action
	 * 
	 * @var array
	 */
	protected $_deniedPage = array(
		'module'     => '',
		'controller' => '',
		'action'     => ''
	);
	
	/**
	 * Default controller prefix which need restricted access
	 * 
	 * @var string
	 */
	protected $_restrictedControllerPrefix = 'admin-';
	
	/**
	 * Login page action
	 * 
	 * @var array
	 */
	protected $_loginPage = array(
		'module'     => '',
		'controller' => '',
		'action'     => ''		
	);
	
	/**
	 * Flag of plugin previous execution is change request
	 * 
	 * @var boolean
	 */
	protected $_triggered = false;
	
	/**
	 * Acl container
	 * 
	 * @var Zend_Acl
	 */
	protected $_acl;
	
	/**
	 * Acl cache
	 * 
	 * @var Zend_Cache_Core
	 */
	protected static $_cache;
	
	public function setAcl(Zend_Acl $acl)
	{
		$this->_acl = $acl;
		return $this;
	}
	
	public function getAcl()
	{
		if (null === $this->_acl) {
			$this->setAcl(new Zend_Acl());
		}
		
		return $this->_acl;
	}
	
	public function getCache()
	{
		if (null !== self::$_cache && self::$_cache->test('Zend_Acl')) {
			$this->_acl = self::$_cache->load('Zend_Acl');
		}
		
		return $this;
	}
	
	public function getCache()
	{
		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		
		if ($bootstrap instanceof Zend_Application_Bootstrap_BootstrapAbstract){}
		$bootstrap->getResource('cachemanager');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Plugin_Abstract::preDispatch()
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$acl = $this->getAcl();
		$recource = $request->getModuleName() . '/' . $request->getControllerName();
		
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_setDispatched($request, $this->_deniedPage);
		} else {
			$this->_setDispatched($request, $this->_loginPage);
		}
	}
	
	/**
	 * Modify request to forward another action
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 * @param array   $resetParams
	 * @param boolean $dispatchedFlag
	 */
	protected function _setDispatched(Zend_Controller_Request_Abstract $request, $resetParams, $dispatchedFlag = false)
	{
		$originalRequest = clone $request;
		$request->clearParams();
		
		$request->setModuleName($resetParams['module']);
		$request->setControllerName($resetParams['controller']);
		$request->setActionName($resetParams['action']);
		$request->setParam(self::ORIGINAL_REQUEST, $originalRequest);
		$request->setDispatched($dispatchedFlag);
		$this->_triggered = true;
	}
}