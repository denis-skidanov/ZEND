<?php

class Sunny_View_Helper_JqGrid extends Zend_View_Helper_Abstract
{
	public function jqGrid($id, $params = array(), $doNotQuoteIdentifier = false)
	{
		$doNotQuoteIdentifier = (bool) $doNotQuoteIdentifier;
		
		if (count($params) > 0) {
			$json = Zend_Json::encode($params);
		} else {
			$json = '{}';
		}
		
		
		if ($doNotQuoteIdentifier) {
			$js = '$(' . $id . ').jqGrid(' . $json . ')';
		} else {
			$js = '$("' . $id . '").jqGrid(' . $json . ')';
		}
		
		return $this->view->inlineScript(Zend_View_Helper_HeadScript::SCRIPT, $js);
	}
}