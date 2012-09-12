<?php

require_once 'Zend/Controller/Plugin/Abstract.php';

class Sunny_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
	protected $_rules = array();
	
	protected $_redirect = array(
		'module'     => 'default',
		'controller' => 'error',
		'action'     => 'auth-error'
	);
	
	public function __construct($options = null)
	{
		if (is_array($options)) {
			if (is_array($options['rules'])) {
				$this->setRules($options['rules']);
			}
			
			if (is_array($options['redirect'])) {
				$this->setRedirect($options['redirect']);
			}
		}
	}
	
	public function setRules(array $options)
	{
		$this->_rules = array();
		
		foreach ($options as $rule) {
			if (!is_array($rule)) {
				continue;
			}
			
			if (isset($rule['module']) || isset($rule['controller']) || isset($rule['action'])) {
				$this->_rules[] = array(
					'module'     => $rule['module'],
					'controller' => $rule['controller'],
					'action'     => $rule['action'],
					
					'redirectModule'     => $rule['redirectModule'],
					'redirectController' => $rule['redirectController'],
					'redirectAction'     => $rule['redirectAction'],
					
					'disabled'   => (bool) $rule['disabled']
				);
			}
		}
		
		return $this;
	}
	
	public function setRedirect(array $options)
	{
		$options = array_intersect_key($options, $this->_redirect);
		$this->_redirect = array_merge($this->_redirect, $options);
		return $this;
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$identity = Zend_Auth::getInstance()->hasIdentity();
		
		foreach ($this->_rules as $rule) {
			if ($rule['disabled']) {
				continue;
			}
			
			$redirectModule = $this->_redirect['module'];
			$redirectController = $this->_redirect['controller'];
			$redirectAction = $this->_redirect['action'];
			
			$block = false;
			
			$moduleMatch = true;
			if (!empty($rule['module'])) {
				$moduleMatch = false;
				if ('/' == substr($rule['module'], 0, 1) && preg_match($rule['module'], $request->getModuleName())) {
					$moduleMatch = true;
				} else if ($rule['module'] == $request->getModuleName()) {
					$moduleMatch = true;
				}
			}
			
			$controllerMatch = true;
			if (!empty($rule['controller'])) {
				$controllerMatch = false;
				if ('/' == substr($rule['controller'], 0, 1) && preg_match($rule['controller'], $request->getControllerName())) {
					$controllerMatch = true;
				} else if ($rule['controller'] == $request->getControllerName()) {
					$controllerMatch = true;
				}
			}
			
			$actionMatch = true;
			if (!empty($rule['action'])) {
				$actionMatch = false;
				if ('/' == substr($rule['action'], 0, 1) && preg_match($rule['action'], $request->getActionName())) {
					$actionMatch = true;
				} else if ($rule['action'] == $request->getActionName()) {
					$actionMatch = true;
				}
			}
			
			if (!empty($rule['redirectModule'])) {
				$redirectModule = $rule['redirectModule'];
			}
			
			if (!empty($rule['redirectController'])) {
				$redirectController = $rule['redirectController'];
			}
			
			if (!empty($rule['redirectAction'])) {
				$redirectAction = $rule['redirectAction'];
			}
			
			if ($moduleMatch && $controllerMatch && $actionMatch && !$identity) {
				$request->setModuleName($redirectModule);
        		$request->setControllerName($redirectController);
        		$request->setActionName($redirectAction);
        		$request->setDispatched(true);
				return;
			}
		}
	}
}
