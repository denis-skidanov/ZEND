<?php

class Sunny_Controller_Action extends Zend_Controller_Action
{
	/** Session var names constants */
	const SESSION_PAGE = 'SESSION_PAGE';
	const SESSION_ROWS = 'SESSION_ROWS';
	const SESSION_FILT = 'SESSION_FILT';
	
	/**
	 * Default page number
	 * 
	 * @var integer
	 */
	protected $_default_page = 1;
	
	/**
	 * Default rows count
	 * 
	 * @var integer
	 */
	protected $_default_rows = 20;
	
	/**
	 * Default filters array
	 * 
	 * @var array
	 */
	protected $_default_filt = array();
	
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
	
	protected static $_cache;
	
	protected $_useCache = true;
	
	public function setUseCache($flag = true)
	{
		$this->_useCache = (bool) $flag;
		return $this;
	}
	
	public function isUseCache()
	{
		$request = $this->getRequest();
		
		if ($request->isXmlHttpRequest() || $request->isPost() ||  null === self::getCache()) {
			//return false;
		}
		
		return $this->_useCache;
	}
	
	public static function setCache(Zend_Cache_Core $obj)
	{
		self::$_cache = $obj;
	}
	
	public static function getCache()
	{
		return self::$_cache;
	}
	
	protected function _makeCacheId()
	{
		$request = $this->getRequest();
		$params = $request->getParams();
		
		return 'CONTROLLER_CACHE_' . md5($this->view->url($params, null, true));
	}
	
	public function preDispatch()
	{
		if (!$this->isUseCache()) {
			return;
		}
		
		if (!(self::getCache()->test($this->_makeCacheId()))) {
			return;
		}
		
		$data = self::getCache()->load($this->_makeCacheId());
		
		$this->getResponse()->setBody($data);
		$this->getRequest()->setDispatched(true);
		$this->_helper->viewRenderer->setNoRender();
	}
	
	public function postDispatch()
	{
		if (!$this->isUseCache()) {
			return;
		}
		
		if (!(self::getCache()->test($this->_makeCacheId()))) {
			$this->_helper->viewRenderer->postDispatch();
			self::getCache()->save($this->getResponse()->getBody(), $this->_makeCacheId());
		}
	}
	
	
	/**
	 * Get controller session namespace
	 * If undefined crete it
	 * 
	 * @return Zend_Session_Namespace
	 */
	protected function _getSession()
	{
		if (null === $this->_session) {
			$this->_setSession(new Zend_Session_Namespace(get_class($this)));
		}
		
		return $this->_session;
	}
	
	/**
	 * Get controller session namespace
	 * If undefined crete it
	 * 
	 * @return Zend_Session_Namespace
	 */
	protected function _setSession(Zend_Session_Namespace $session)
	{
		$this->_session = $session;
	}
	
	/**
	 * Get controller session namespace param
	 * If undefined - return $default value
	 * 
	 * @param  string $name    Param name
	 * @param  mixed  $default Default value of param
	 * @return mixed
	 */
	protected function _getSessionParam($name, $default = null)
	{
		if (!isset($this->_getSession()->{$name})) {
			return $default;
		}
		
		return $this->_getSession()->{$name};
	}
	
	/**
	 * Set controller session namespace param
	 * 
	 * @param string $name  Name of parameter
	 * @param mixed  $value New value
	 */
	public function _setSessionParam($name, $value)
	{
		$this->_getSession()->{$name} = $value;
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