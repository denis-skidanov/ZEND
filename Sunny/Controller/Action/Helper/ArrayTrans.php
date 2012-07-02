<?php

/**
 * Print massive into fenschui view
 * @author Maximka
 *
 */
class Sunny_Controller_Action_Helper_ArrayTrans extends Zend_Controller_Action_Helper_Abstract
{
	
	public function direct($data = null)
	{
		require_once 'Zend/Controller/Action/HelperBroker.php';
		$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		return $view->arrayTrans($data);
	}
}