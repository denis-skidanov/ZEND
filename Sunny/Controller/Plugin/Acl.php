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
	
	protected function _buildAcl()
	{
		$groupsMapper = new Users_Model_Mapper_UsersGroups();
		$groups = $groupsMapper->fetchAll();
		
		foreach ($groups as $role) {
			$this->getAcl()->addRole('users_groups_' . $role->id);
		}
		
		$permissionsMapper = new Users_Model_Mapper_UsersPermissions();
		$permissions = $permissionsMapper->fetchAll();
		foreach ($permissions as $resource) {
			$this->getAcl()->addResource('users_permissions_' . $resource->id);
		}
		
		$groupsPermissionsMapper = new Users_Model_Mapper_UsersGroupsPermissions();
		$rules = $groupsPermissionsMapper->fetchAll(array(
			'allow = ?' => 'YES'
		));
		foreach ($rules as $rule) {
			$this->getAcl()->allow('users_groups_' . $rule->users_groups_id, 'users_permissions_' . $rule->users_permissions_id);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Plugin_Abstract::preDispatch()
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$this->_buildAcl();
		$acl = $this->getAcl();
		
		if (!Zend_Auth::getInstance()->hasIdentity()) {
			$this->_setDispatched($request, $this->_deniedPage);
			return;
		}
		
		if (preg_match('/^admin/', $request->getControllerName())) {
			$mapper = new Users_Model_Mapper_UsersPermissions();
			$resource = $mapper->findResource($request->getModuleName() . '/' . $request->getControllerName());
			
			$user = Zend_Auth::getInstance()->getIdentity();
			$role = $mapper->findRole($user);
			
			if (!$resource || !$role || !$acl->isAllowed('users_groups_' . $role->id, 'users_permissions_' . $resource->id)) {
				$this->_setDispatched($request, $this->_deniedPage);
				return;
			}
		}
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
		
		Zend_Controller_Action_HelperBroker::getStaticHelper('layout')->setLayout('admin-layout');
		
		$request->setModuleName($resetParams['module']);
		$request->setControllerName($resetParams['controller']);
		$request->setActionName($resetParams['action']);
		$request->setParam(self::ORIGINAL_REQUEST, $originalRequest);
		$request->setDispatched($dispatchedFlag);
		$this->_triggered = true;
	}
}