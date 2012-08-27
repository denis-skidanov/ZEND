<?php

require_once 'Zend/Session.php';

class Sunny_SwfSession extends Zend_Controller_Plugin_Abstract
{
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
    	$sid = $request->getParam('PHPSESSID');
    	if (empty($sid)) {
    		// If empty or not isset session id - do nothing
    		return;
    	}
    	
    	if (Zend_Session::sessionExists()) {
    		// if session exists - cannot rewrite id
    		//return;
    	}
    	
    	// Rewrite session id to be sure that we use valid session
    	Zend_Session::setId($sid);
    }
}

