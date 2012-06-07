<?php
class Sunny_ZendViewHelperVarExport extends Zend_View_Helper_Abstract
{
	/**
	 * Prints a string representation of variable
	 * 
	 * @param mixed $var
	 * @return string
	 */
	public function varExport($var)
	{
		echo '<pre>';
		var_export($var);
		echo '</pre>';
	}
}