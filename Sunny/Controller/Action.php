<?php

class Sunny_Controller_Action extends Zend_Controller_Action
{
	const SESSION_PAGE   = 'page';
	const SESSION_ROWS   = 'rows';
	const SESSION_FILTER = 'filter';
	
	protected $_session;
	
	public function getSession()
	{
		if (null === $this->_session) {
			$this->setSession(new Zend_Session_Namespace(get_class($this)));
		}
		
		return $this->_session;
	}
	
	public function setSession(Zend_Session_Namespace $session)
	{
		$this->_session = $session;
		return $this;
	}
	
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