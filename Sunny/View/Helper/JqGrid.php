<?php

class Sunny_View_Helper_JqGrid extends Zend_View_Helper_Abstract
{
	public function jqGrid($id, $params = array(), $doNotQuoteIdentifier = false)
	{
	    if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = false;
        }
				
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
		
		$html  = '<script type="text/javascript">' . PHP_EOL;
        $html .= ($useCdata) ? '//<![CDATA[' : '//<!--';
		$html .= PHP_EOL . $js . PHP_EOL;		
        $html .= ($useCdata) ? '//]]>'       : '//-->';
		$html .= PHP_EOL . '</script>' . PHP_EOL;
		return $html;
	}
}