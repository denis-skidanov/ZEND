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
	
	protected function _htmlifyMessages()
	{
		$xhtml = '';
	
		if ($this->_helper->flashMessenger->hasMessages()) {
			foreach ($this->_helper->flashMessenger->getMessages() as $message) {
				$xhtml .= '<div class="flash-messenger-message ui-corner-all" style="display: none;">' . $message . '</div>';
			}
		}
	
		$this->_helper->flashMessenger->clearMessages();
	
		if ($this->_helper->flashMessenger->hasCurrentMessages()) {
			foreach ($this->_helper->flashMessenger->getCurrentMessages() as $message) {
				$xhtml .= '<div class="flash-messenger-message ui-corner-all" style="display: none;">' . $message . '</div>';
			}
		}
	
		$this->_helper->flashMessenger->clearCurrentMessages();
	
		return $xhtml;
	}
	
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
	
	protected function _changeIgnoredStatus($id)
	{
		$entity = $this->_getMapper()->findEntity($id);
		
		if($entity->ignored == 1) {
    		$entity->__set('ignored', 0);
    	} else {
    		$entity->__set('ignored', 1);
    	}
		
		$this->_getMapper()->saveEntity($entity);
	}
	
	protected function _changePublishedStatus($id)
	{
		$entity = $this->_getMapper()->findEntity($id);
	
		if($entity->published == 1) {
			$entity->__set('published', 0);
		} else {
			$entity->__set('published', 1);
		}
	
		$this->_getMapper()->saveEntity($entity);
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
	* Generates list of element "select" like tree in Zend_Form
	* Generate tree like named options list for "Multi" form elements
	*
	* @param  Sunny_DataMapper_CollectionAbstract $collection  Input collection
	* @param  array                               $exclude     Array of excluded identifiers
	* @param  array                               $result      Previous result or pre defined options
	* @param  array                               $level       Tree deep level
	* @return array  Result options
	*/
	public function collectionToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
	
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->title;
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	/**
	* Generates list of element "select" like tree in Zend_Form
	* Generate tree like named options list for "Multi" form elements
	*
	* @param  Sunny_DataMapper_CollectionAbstract $collection  Input collection
	* @param  array                               $exclude     Array of excluded identifiers
	* @param  array                               $result      Previous result or pre defined options
	* @param  array                               $level       Tree deep level
	* @return array  Result options
	*/
	public function onecEmployeesToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
	
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->sname . ' ' . $entity->name;
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	public function contentsTitlesToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
	
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->title . ' (' . $entity->languagesAlias . ')';
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	public function sheduleStripsToMultiOptions(Sunny_DataMapper_CollectionAbstract $collection = null, $exclude = array(), $result = array(), $level = 0)
	{
		if (null === $collection) {
			return $result;
		}
	
		foreach ($collection as $entity) {
			if (!in_array($entity->id, $exclude)) {
				$titleOffset = str_repeat('--', $level);
	
				$result[$entity->id] = $titleOffset . ' ' . $entity->lesson_number;
				if (count($entity->getExtendChilds()) > 0) {
					$result = $this->collectionToMultiOptions($entity->getExtendChilds(), $exclude, $result, $level + 1);
				}
			}
		}
	
		return $result;
	}
	
	public function timeToDb($string) 
	{
		$time = explode(':', $string);
		if (count($time) == 2) {
			return mktime((int) $time[0], (int) $time[1], 0, 1, 1, 1970);
		}
	}
	
	public function timeFromDb($string)
	{
		if (is_null($string) || empty($string)) {
			return date("G:i", time());
		}
		
		return date("G:i", $string);
	}
	
	
	/**
	 * Making timestamp from string h:i:m d-m-Y
	 * Enter description here ...
	 * @param unknown_type $string
	 */
	public function fulltimeToDb($string = null)
	{
		if (is_null($string)) {
			return time();
		}
		
		$preArray = explode(' ', $string);
		$timeArray = explode(':', $preArray[0]);
		$dateArray = explode('-', $preArray[1]);
		
		if (count($timeArray) != 2 || count($dateArray) != 3) {
			return time();
		}
		
		return mktime($timeArray[0], $timeArray[1], 0, $dateArray[1], $dateArray[0], $dateArray[2]);
	}
	
	public function fulltimeFromDb($string = null)
	{
		if (is_null($string) || empty($string)) {
			return date("G:i j-n-Y", time());
		}
	
		return date("G:i j-n-Y", $string);
	}
	
	public function dateToDb($string = null)
	{
		if (is_null($string)) {
			return time();
		}
		$dateArray = explode('-', $string);
	
		if (count($dateArray) != 3) {
			return time();
		}
	
		return mktime(0, 0, 1, $dateArray[1], $dateArray[0], $dateArray[2]);
	}
	
	public function dateFromDb($string = null)
	{
		if (is_null($string) || empty($string)) {
			return date("j-n-Y", time());
		}
	
		return date("j-n-Y", $string);
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