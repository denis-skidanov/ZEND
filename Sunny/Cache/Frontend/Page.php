<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Frontend
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Page.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @see Zend_Cache_Core
 */
require_once 'Zend/Cache/Core.php';


/**
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Frontend
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sunny_Cache_Frontend_Page extends Zend_Cache_Core
{
    /**
     * Internal id
     * 
     * @var string
     */
    protected $_makedId;

    /**
     * Extended constructor
     * 
     * @param array|Zend_Config $options Associative array of options or Zend_Config instance
     */
    public function __construct($options = array())
    {
    	$this->_specificOptions['debug_header'] = false;
    	parent::__construct($options);
    }
    
    /**
     * Start the cache
     *
     * @param  string  $id       (optional) A cache id (if you set a value here, maybe you have to use Output frontend instead)
     * @param  boolean $doNotDie For unit testing only !
     * @return boolean True if the cache is hit (false else)
     */
    public function start()
    {
    	if (!$this->_options['caching']) {
    		return true;
    	}
    	
    	$test = $this->test($this->_makeId());
    	if (false !== $test) {
	        if ($this->_specificOptions['debug_header']) {
	            echo '<!-- DEBUG HEADER : This is a cached page ! -->';
	        }
	            
	        echo $this->load($this->_makeId());	         
	        die();
	    }
        
        ob_start(array($this, '_flush'));
        //ob_implicit_flush(false);
        return false;
    }

    /**
     * callback for output buffering
     * (shouldn't really be called manually)
     *
     * @param  string $data Buffered output
     * @return string Data to send to browser
     */
    public function _flush($data)
    {
        $this->save($data, $this->_makeId());
        return $data;
    }

    /**
     * Make an id depending on REQUEST_URI
     *
     * @return string a cache id
     */
    protected function _makeId()
    {
        if (null === $this->_makedId) {
        	$this->_makedId = md5('/' . trim($_SERVER['REQUEST_URI'], '/'));
        }
        
        return $this->_makedId;
    }
}
