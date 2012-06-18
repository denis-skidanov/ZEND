<?php

require_once 'Zend/View/Helper/Url.php';

class Sunny_View_Helper_SimpleUrl extends Zend_View_Helper_Url
{
	/**
	 * Simple generates an url given the name of a route.
	 * Extending view url helper
	 * 
	 * @param string  $action     Action name
	 * @param string  $controller OPTIONAL Controller name
	 * @param string  $module     OPTIONAL Module name
	 * @param array   $params     OPTIONAL Additional params
	 * @param string  $name       OPTIONAL Name of a route to assemble
	 * @return string Generated url (for href or src attribute)
	 */
	public function simpleUrl($action, $controller = null, $module = null, array $params = null, $name = null)
	{
		$urlOptions = array('action' => $action);
	    
	    if (null !== $controller) {
            $urlOptions['controller'] = $controller;
        }
	
        if (null !== $module) {
            $urlOptions['module'] = $module;
        }
        
        
        if (null !== $params) {
        	$params = array_diff_key($params, $urlOptions);
        	$urlOptions = array_merge($urlOptions, $params);
        }
		
		return $this->url($urlOptions, $name, true, true);
	}
}