<?php

class Sunny_Form_Decorator_FileSelectorDiv extends Sunny_Form_Decorator_CompositeElementDiv
{
	const MODE_FILE  = 'file';
	const MODE_IMAGE = 'image';
	
	protected $_modes = array(
		self::MODE_FILE,
		self::MODE_IMAGE
	);
	
	/**
	 * Render html form element tag
	 * 
	 * @return string XHTML
	 */
	public function buildElement()
	{
		$e      = $this->getElement();
		$view   = $e->getView();
		$helper = $e->helper;		
		
		$type = $e->getType();
		
		$attribs = $e->getAttribs();
		
		$mode = self::MODE_FILE;
		if (is_string($attribs['selector_mode']) && in_array($attribs['selector_mode'], $this->_modes)) {
			$mode = $attribs['selector_mode'];
		}
		$attribs['selector_mode'] = $mode;
		
		$buttonLabel = 'Select ' . $mode;
		if (is_string($attribs['buttonLabel'])) {
			$buttonLabel = $attribs['buttonLabel'];
			unset ($attribs['buttonLabel']);
		}
		
		$selectMultiple = 'false';
		$jsMethod = 'mainImageRenderer';
		if (isset($attribs['selectMultiple']) && !!$attribs['selectMultiple']) {
			$selectMultiple = 'true';
			$jsMethod = 'imagesRenderer';
		}
		
		$imgType = '';
		if (is_string($attribs['media-type'])) {
			$imgType = $attribs['media-type'];
			unset ($attribs['media-type']);
		}
		
		$xhtml = '<div class="' . $this->_namespace . '-tag">'
			   . $view->formHidden($e->getName(), $e->getValue(), array('media-type' => $imgType, 'select-multiple' => $selectMultiple, 'autocomplete' => "off"))
			   . $view->$helper($e->getName() . '-button', $buttonLabel, $attribs, $e->options)
			   . '<div class="' . $e->getName() . '-list-container ' . $this->_namespace . '-mode-' . $mode . '">'
			   . '<ul class="' . $e->getName() . '-list"></ul>'
			   . '</div>'
			   . '<script>$(document).ready(function(){ $.fn.cmsManager(\'' . $jsMethod . '\', null, \'' . $e->getName() . '\'); })</script>'
			   . '</div>';			
	
		return $xhtml;
	}
	
}