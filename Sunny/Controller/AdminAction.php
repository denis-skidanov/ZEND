<?php

class Sunny_Controller_AdminAction extends Zend_Controller_Action
{
	/** Session var names constants */
	const SESSION_PAGE   = 'SESSION_PAGE';
	const SESSION_ROWS   = 'SESSION_ROWS';
	const SESSION_FILTER = 'SESSION_FILTER';
	
	/**
	 * Internal default mapper container
	 * 
	 * @var Sunny_DataMapper_MapperAbstract
	 */
	protected $_mapper;
	
	/**
	 * Mapper name
	 * 
	 * @var string
	 */
	protected $_mapperName;
	
	/**
	 * Requested module name
	 * @var string
	 */
	protected $_m;
	
	/**
	 * Requested controller name
	 * @var string
	 */
	protected $_c;
	
	/**
	 * Requested action name
	 * @var string
	 */
	protected $_a;
	
	/**
	 * Internal session container
	 * 
	 * @var Zend_Session_Namespace
	 */
	protected $_session;
	
	/**
	 * Filter defaults
	 * 
	 * @var array
	 */
	protected $_filters = array();
	
	/**
	 * Get session namespace
	 * 
	 * @return Zend_Session_Namespace
	 */
	protected function _getSession()
	{
		if (null === $this->_session) {
			$this->_session = new Zend_Session_Namespace(get_class($this));
		}
		
		return $this->_session;
		
	}
	
	/**
	 * 
	 */
	protected function _getSessionPage($namespace = null)
	{
		$namespace = (string) $namespace;
		if (!isset($this->_getSession()->{self::SESSION_PAGE . $namespace})) {
			$this->_getSession()->{self::SESSION_PAGE . $namespace} = 1;
		}
		
		return $this->_getSession()->{self::SESSION_PAGE . $namespace};
	}
	
	/**
	 * 
	 */
	protected function _setSessionPage($page, $namespace = null)
	{
		$this->_getSession()->{self::SESSION_PAGE . $namespace} = $page;
		return $this;
	}
	
	/**
	 * 
	 */
	protected function _getSessionRows($namespace = null)
	{
		$namespace = (string) $namespace;
		if (!isset($this->_getSession()->{self::SESSION_ROWS . $namespace})) {
			$this->_getSession()->{self::SESSION_ROWS . $namespace} = 20;
		}
		
		return $this->_getSession()->{self::SESSION_ROWS . $namespace};
	}
	
	/**
	 * 
	 */
	protected function _setSessionRows($rows, $namespace = null)
	{
		$this->_getSession()->{self::SESSION_ROWS . $namespace} = $rows;
		return $this;
	}
	
	/**
	 * 
	 */
	protected function _getSessionFilter($name = null, $namespace = null)
	{
		$namespace = (string) $namespace;
		if (!isset($this->_getSession()->{self::SESSION_FILTER . $namespace})) {
			$this->_getSession()->{self::SESSION_FILTER . $namespace} = (array) $this->_filters;
		}
		
		if (is_string($name) && array_key_exists($name, $this->_filters)) {
			$params = $this->_getSession()->{self::SESSION_FILTER . $namespace};
			return $params[$name];
		}
		
		return $this->_getSession()->{self::SESSION_FILTER . $namespace};
	}
	
	/**
	 * 
	 */
	protected function _setSessionFilter($name, $value = null, $namespace = null)
	{
		$namespace = (string) $namespace;

		if (is_string($name) && array_key_exists($name, $this->_filters)) {
			$params = $this->_getSession()->{self::SESSION_FILTER . $namespace};
			$params[$name] = $value;
			$this->_getSession()->{self::SESSION_FILTER . $namespace} = $params;
		} else if (is_array($name)) {
			$params = (array) $this->_getSession()->{self::SESSION_FILTER . $namespace};
			
			foreach ($name as $key => $value) {
				if (array_key_exists($key, $this->_filters)) {
					$params[$key] = $value;
				}
			}
			
			$this->_getSession()->{self::SESSION_FILTER . $namespace} = $params;
		}		
		
		return $this;
	}
	
	/**
	 * Get default mapper
	 * 
	 * @return Sunny_DataMapper_MapperAbstract
	 */
	protected function _getMapper()
	{
		if (null === $this->_mapperName) {
			throw new Zend_Controller_Action_Exception("Default mapper name not defined", 500);
		}
		
		if (null === $this->_mapper) {
			$mapper = $this->_mapperName;
			$this->_mapper = new $mapper();
		}
		
		return $this->_mapper;
	}
	
	/**
	 * Goto url on ajax/header redirect by request header value
	 * 
	 * (non-PHPdoc)
	 * @see Sunny_View_Helper::simpleUrl()
	 */
	protected function _gotoUrl($action, $controller = null, $module = null, array $params = null, $name = null)
	{
		$url = $this->view->simpleUrl($action, $controller, $module, $params, $name);
		if ($this->getRequest()->isXmlHttpRequest()) {
			$this->view->redirectTo = $url;
		} else {
			$this->_helper->redirector->gotoUrl($url);
		}
	}
	
	/**
	 * 
	 * Creates a data structure for the responder
	 * @param string $action
	 * @param string $controller
	 * @param string $module
	 * @param array $params
	 * @param string $method
	 * @param string $container
	 * @param string $source
	 */
	protected function _makeResponderStructure($action = null, $controller = null, $module = null, $params = array(), $method = 'redirect', $container = null, $source = null)
	{
		if (is_null($module)) {
			$module = $this->_m;
		}
		
		if (is_null($controller)) {
			$controller = $this->_c;
		}
		
		if (is_null($action)) {
			$action = $this->_a;
		}
		
		$this->view->actions = (array) $this->view->actions;
		$this->view->actions[] = array(
			$method => array(
				'container' => $container,
				'url' => array(
					"m" => $module,
					"c" => $controller,
					"a" => $action,
					'params' => $params
				),
				'source' => $source
			)
		);
	}
	
	/**
	 * Abstract initialization
	 * If need extending use parent::init() in controller init()
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Controller_Action::init()
	 */
	public function init()
	{
		// Forse ajax requests disable layout rendering
		if ($this->getRequest()->isXmlHttpRequest()) {
			$this->_helper->layout()->disableLayout();
		}
		
		// Populate requested action to controller for url build
		$this->_a = $this->getRequest()->getActionName();
		$this->_c = $this->getRequest()->getControllerName();
		$this->_m = $this->getRequest()->getModuleName();
		
		// Populate requested action to view for url build
		$this->view->a = $this->_a;
		$this->view->c = $this->_c;
		$this->view->m = $this->_m;
	}
}