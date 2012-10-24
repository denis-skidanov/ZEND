<?php

require_once 'Zend/Acl.php';

require_once 'Zend/Auth.php';

require_once 'Zend/Controller/Plugin/Abstract.php';

require_once 'Zend/Controller/Request/Abstract.php';

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
		'module'     => 'default',
		'controller' => 'error',
		'action'     => 'acl-error'
	);
	
	/**
	 * Default controller prefix which need restricted access
	 * 
	 * @var string
	 */
	protected $_restrictedControllerPrefix = 'admin-';
	
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
	//protected static $_cache;

	public function __construct($options = null)
	{
		if (is_array($options)) {
			
		}
	}
	
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
	
	/*public function getCache()
	{
		if (null !== self::$_cache && self::$_cache->test('Zend_Acl')) {
			$this->_acl = self::$_cache->load('Zend_Acl');
		}
		
		return $this;
	}*/
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Plugin_Abstract::preDispatch()
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		//$acl = $this->getAcl();
		//$recource = $request->getModuleName() . '/' . $request->getControllerName();
		
		
		
		$this->_setDispatched($request, $this->_deniedPage);
	}
	
	/**
	 * Modify request to forward another action
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 * @param array   $resetParams
	 * @param boolean $dispatchedFlag
	 */
	protected function _setDispatched(Zend_Controller_Request_Abstract $request, $resetParams, $dispatchedFlag = true)
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