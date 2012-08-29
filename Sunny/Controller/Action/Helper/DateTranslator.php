<?php
/**
 * Returns date in current language
 * @author Maximka
 *
 */
class Sunny_Controller_Action_Helper_DateTranslator extends Zend_Controller_Action_Helper_Abstract
{
	
	public function direct($data = null)
	{
		require_once 'Zend/Controller/Action/HelperBroker.php';
		$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		return $view->dateTranslator($data);
	}
}