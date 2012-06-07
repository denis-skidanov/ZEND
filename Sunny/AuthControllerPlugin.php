<?php

require_once 'Zend/Auth.php';

require_once 'Zend/Controller/Plugin/Abstract.php';

class Sunny_AuthControllerPlugin extends Zend_Controller_Plugin_Abstract
{
	/**
	 * Default error module name
	 * @var string
	 */
	protected $_module = 'default';
	
	/**
	 * Default error controller name
	 * @var string
	 */
	protected $_controller = 'error';
	
	/**
	 * Default error action name
	 * @var string
	 */
	protected $_action = 'authorization-error';
	
	/**
	 * Collection of pages witch required authorized acess
	 * @var array
	 */
	protected $_requiredPages = array();
	
	/**
	 * Page options keys
	 * @var array
	 */
	protected $_requiredKeys  = array('module', 'controller', 'action');
	
	/**
	 * Zend_Auth object container
	 * @var Zend_Auth
	 */
	protected $_auth;
	
	/**
	 * Flag if index page not need to be checked
	 * @var boolean
	 */
	protected $_indexAlwaysSkip = false;
	
	/**
	 * Constructor
	 * @param array $options
	 */
	public function __construct($options = array())
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}
	
	/**
	 * Set options at once
	 * @param array $options
	 * @return Sunny_AuthControllerPlugin
	 */
	public function setOptions($options)
	{
		if (isset($options['auth'])) {
			$this->setAuth($options['auth']);
		}
		
		if (isset($options['module'])) {
			$this->setModuleName($options['module']);
		}
		
		if (isset($options['controller'])) {
			$this->setControllerName($options['controller']);
		}
		
		if (isset($options['action'])) {
			$this->setActionName($options['action']);
		}
		
		if (isset($options['requiredPages'])) {
			$this->setRequiredPages($options['requiredPages']);
		}
				
		if (isset($options['indexAlwaysSkip'])) {
			$this->_indexAlwaysSkip = (bool) $options['indexAlwaysSkip'];
		}
		
		return $this;
	}
	
	/**
	 * Set internal auth object
	 * @param Zend_Auth $auth
	 * @return Sunny_AuthControllerPlugin
	 */
	public function setAuth(Zend_Auth $auth)
	{
		$this->_auth = $auth;
		return $this;
	}
	
	/**
	 * Get internal auth object
	 * @return Zend_Auth
	 */
	public function getAuth()
	{
		if (null === $this->_auth) {
			$this->setAuth(Zend_Auth::getInstance());
		}
		
		return $this->_auth;
	}
	
	/**
	 * Set collection of pages witch required authorized acess at once
	 * @param array $pages
	 * @return Sunny_AuthControllerPlugin
	 */
	public function setRequiredPages($pages = array())
	{
		$this->_requiredPages = array();
		foreach ((array) $pages as $page) {
			$this->addRequiredPage($page);
		}
		
		return $this;
	}
	
	/**
	 * Get collection of pages witch required authorized acess
	 * @return array
	 */
	public function getRequiredPages()
	{
		return $this->_requiredPages;
	}
	
	/**
	 * Add page witch required authorized acess to collection
	 * @param array $options
	 * @return Sunny_AuthControllerPlugin
	 */
	public function addRequiredPage($options = array())
	{
		$pageKeys = array_flip($this->_requiredKeys);
		$options = array_intersect_key($pageKeys, $options);
		$options = array_merge($pageKeys, $options);
		
		$this->_requiredPages[] = $options;
		return $this;
	}
	
	/**
	 * Set default module name
	 * @param string $module
	 * @return Sunny_AuthControllerPlugin
	 */
	public function setModuleName($module)
	{
		$this->_module = (string) $module;
		return $this;
	}
	
	/**
	 * Get default module name
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->_module;
	}
	
	/**
	 * Set default controller name
	 * @param string $controller
	 * @return Sunny_AuthControllerPlugin
	 */
	public function setControllerName($controller)
	{
		$this->_controller = (string) $controller;
		return $this;
	}
	
	/**
	 * Get default controller name
	 * @return string
	 */
	public function getControllerName()
	{
		return $this->_controller;
	}
	
	/**
	 * Set default action name
	 * @param string $action
	 * @return Sunny_AuthControllerPlugin
	 */
	public function setActionName($action)
	{
		$this->_action = (string) $action;
		return $this;
	}
	
	/**
	 * Get default action name
	 * @return string
	 */
	public function getActionName()
	{
		return $this->_action;
	}
	
	/**
	 * Plugin pre dispatch process
	 * @see Zend_Controller_Plugin_Abstract::preDispatch()
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{        
		// Get front controller
		require_once 'Zend/Controller/Front.php';
		$front = Zend_Controller_Front::getInstance();

		if ($front->getParam('noErrorHandler')) {
			return;
		}
		
		// If inside error controller - skip
		$errorHandler = $front->getPlugin('Zend_Controller_Plugin_ErrorHandler');
		if ($request->getModuleName() == $errorHandler->getErrorHandlerModule()
			&& $request->getControllerName() == $errorHandler->getErrorHandlerController()
			&& $request->getActionName() == $errorHandler->getErrorHandlerAction()) {
			//echo $request->getActionName();
			return;
		}
		
		// Check if requested error page - do nothing
		if ($request->getModuleName() == $this->getModuleName()
			&& $request->getControllerName() == $this->getControllerName()
			&& $request->getActionName() == $this->getActionName()) {
			//echo $this->getActionName();
			return;
		}
		
		if (!$this->_indexAlwaysSkip) {
			// If index must be not skipped add to pages
			$this->_requiredPages[] = array(
				'module'     => $front->getDefaultModule(),
				'controller' => $front->getDefaultControllerName(),
				'action'     => $front->getDefaultAction()
			);			
		} else {
			// Check if index page requested and not need authorized acess
			if ($request->getModuleName() == $front->getDefaultModule()
				&& $request->getControllerName() == $front->getDefaultControllerName()
				&& $request->getActionName() == $front->getDefaultAction()) {
				return;
			}
		}
		
		$required = false;
		foreach ($this->_requiredPages as $page) {
			// check if page module requested
			if ($page['module'] == $request->getModuleName()) {
				// check if page controller requested
				if ($page['controller'] == $request->getControllerName()) {
					// check if page action requested
					if ($page['action'] == $request->getActionName()) {
						$required = true;
					} else if ($page['action'] == '*') {
						$required = true;
					}
				} else if ($page['controller'] == '*') {
					$required = true;
				}
			} else if ($page['module'] == '*') {
				$required = true;
			}
		}
		
		//var_export($request->getModuleName());
		//var_export($request->getControllerName());
		//var_export($required);
		
		// Check if authorization identifier not exists
		if ($required && !$this->getAuth()->hasIdentity()) {
			$this->setNotAutentificated($request);
		}
	}
	
	/**
	 * Change request to error page and redirect to it
	 * @param Zend_Controller_Request_Abstract $request
	 */
	public function setNotAutentificated($request)
	{
		// Clone original request for debug possible
		$original = clone $request;
		
		// Set error module/controller/action to request for redirect
		$request->setModuleName($this->getModuleName());
		$request->setControllerName($this->getControllerName());
		$request->setActionName($this->getActionName());
		$request->setParam('original', $original);
		
		// Set request to undispatched, this runs redirect to error page
		$request->setDispatched(false);
	}
}