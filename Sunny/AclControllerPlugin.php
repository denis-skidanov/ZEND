<?php

/** Zend_Acl */
require_once 'Zend/Acl.php';

/** Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

class Sunny_AclControllerPlugin extends Zend_Controller_Plugin_Abstract
{
	protected $_module     = 'default';	
	protected $_controller = 'error';	
	protected $_action     = 'acl-error';
	
	protected $_acl;
    protected $_roleName;

    public function __construct($options = array())
    {
    	if (is_array($options)) {
    		if (isset($options['acl'])) {
    			$this->setAcl($options['acl']);
    		}
    		
    		if (isset($options['role'])) {
    			$this->setRoleName($options['role']);
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
    	}
    }
    
    public function setModuleName($module)
    {
    	$this->_module = (string) $module;
    	return $this;
    }
    
    public function getModuleName()
    {
    	return $this->_module;
    }
    
    public function setControllerName($controller)
    {
    	$this->_controller = (string) $controller;
    	return $this;
    }
    
    public function getControllerName()
    {
    	return $this->_controller;
    }
    
    public function setActionName($action)
    {
    	$this->_action = (string) $action;
    	return $this;
    }
    
    public function getActionName()
    {
    	return $this->_action;
    }
    
    public function setAcl(Zend_Acl $aclData)
    {
        $this->_acl = $aclData;
    }

    public function getAcl()
    {
        return $this->_acl;
    }

    public function setRoleName($roleName)
    {
        $this->_roleName = (string) $roleName;
    }

    public function getRoleName()
    {
        return $this->_roleName;
    }

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
			&& $request->getControllerName() == $errorHandler->getErrorHandlerController()) {
			//echo $request->getActionName();
			return;
		}
		
    	if ($request->getModuleName() == $this->getModuleName()
			&& $request->getControllerName() == $this->getControllerName()
			&& $request->getActionName() == $this->getActionName()) {
   			// If already redirected - skip
			return;
		}
		//echo 'disallowed';
		
		if (null === $this->getAcl()) {
			return;
		}
    	$resourceName = $request->getModuleName() . '/' . $request->getControllerName() . '/' . $request->getActionName();
        /** TODO: rewrite Check if the controller/action can be accessed by the current user */
        if (!$this->getAcl()->isAllowed($this->getRoleName(), $resourceName)) {
            $this->setAccessDenied($request);
        }
    }

    public function setAccessDenied($request)
    {
        $original = clone $request;
			
		$request->setModuleName($this->getModuleName());
		$request->setControllerName($this->getControllerName());
		$request->setActionName($this->getActionName());
		$request->setParam('original', $original);
		$request->setDispatched(false);		
    }
}