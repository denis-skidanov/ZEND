<?php

/**
 * Print massive into fenschui view
 * @author Maximka
 *
 */
class Sunny_View_Helper_ArrayTrans extends Zend_View_Helper_Abstract
{
	public function arrayTrans($data = null)
	{
		return '<pre>' . var_export($data, true) . '</pre>';
	}
}