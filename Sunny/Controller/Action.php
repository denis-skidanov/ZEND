<?php

class Sunny_Controller_Action extends Zend_Controller_Action
{
	
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
}