<?php

class Sunny_Controller_Action extends Zend_Controller_Action
{
	/** Session var names constants */
	const SESSION_PAGE   = 'page';
	const SESSION_ROWS   = 'rows';
	const SESSION_FILTER = 'filter';
	
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
	 * Get controller session namespace
	 * If undefined crete it
	 * 
	 * @return Zend_Session_Namespace
	 */
	public function getSession()
	{
		if (null === $this->_session) {
			$this->setSession(new Zend_Session_Namespace(get_class($this)));
		}
		
		return $this->_session;
	}
	
	/**
	 * Set controller session namespace
	 * 
	 * @param Zend_Session_Namespace $session
	 */
	public function setSession(Zend_Session_Namespace $session)
	{
		$this->_session = $session;
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
		
		// Populate requested action to view
		$this->view->action     = $this->getRequest()->getActionName();
		$this->view->controller = $this->getRequest()->getControllerName();
		$this->view->module     = $this->getRequest()->getModuleName();
		
		// Populate requested action to controller for url build
		$this->_a = $this->getRequest()->getActionName();
		$this->_c = $this->getRequest()->getControllerName();
		$this->_m = $this->getRequest()->getModuleName();
		
		// Populate requested action to view for url build
		$this->view->a = $this->_a;
		$this->view->c = $this->_c;
		$this->view->m = $this->_m;
		
		// Setup session defaults
		$session = $this->getSession();
		if (!isset($session->{self::SESSION_PAGE})) {
			$session->{self::SESSION_PAGE} = 1;
		}
		
		if (!isset($session->{self::SESSION_ROWS})) {
			$session->{self::SESSION_ROWS} = 20;
		}
		
		if (!isset($session->{self::SESSION_FILTER})) {
			$session->{self::SESSION_FILTER} = array();
		}
	}
}