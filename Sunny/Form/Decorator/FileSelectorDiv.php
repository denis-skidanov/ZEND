<?php

class Sunny_Form_Decorator_FileSelectorDiv extends Sunny_Form_Decorator_CompositeElementDiv
{
	const MODE_FILE  = 'file';
	const MODE_IMAGE = 'image';
	const MODE_VIDEO = 'video';
	
	protected $_modes = array(
		self::MODE_FILE,
		self::MODE_IMAGE,
		self::MODE_VIDEO
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
		
		$selectorMode = self::MODE_FILE;
		if (is_string($attribs['selectorMode']) && in_array($attribs['selectorMode'], $this->_modes)) {
			$selectorMode = $attribs['selectorMode'];
			
		}
		unset($attribs['selectorMode']);
		
		$buttonLabel = 'Select ' . $selectorMode;
		if (is_string($attribs['buttonLabel'])) {
			$buttonLabel = $attribs['buttonLabel'];
			
		}
		unset ($attribs['buttonLabel']);
		
		$selectMultiple = 'false';
		$jsMultiple = 'Single';
		if (isset($attribs['selectMultiple']) && !!$attribs['selectMultiple']) {
			$selectMultiple = 'true';
			$jsMultiple = 'Many';
		}
		unset($attribs['selectMultiple']);
		
		$imgType = '';
		if (is_string($attribs['media-type'])) {
			$imgType = $attribs['media-type'];
			unset ($attribs['media-type']);
		}
		
		$path = '';
		if (is_string($attribs['media-path'])) {
			$path = $attribs['media-path'];
			unset ($attribs['media-path']);
		}
		
		$jsMethod = 'render' . ucfirst($selectorMode) . $jsMultiple;
		
		$xhtml = '<div class="' . $this->_namespace . '-tag">'
			   . $view->formHidden($e->getName(), $e->getValue(), array('media-type' => $imgType, 'media-path' => $path, 'selector-mode' => $selectorMode, 'select-multiple' => $selectMultiple, 'autocomplete' => "off"))
			   . $view->$helper($e->getName() . '-button', $buttonLabel, $attribs, $e->options)
			   . '<div class="' . $e->getName() . '-list-container ' . $this->_namespace . '-mode-' . $selectorMode . '">'
			   . '<ul class="' . $e->getName() . '-list"></ul>'
			   . '</div>'
			   . '<script>$(document).ready(function(){ $.fn.cmsManager(\'' . $jsMethod . '\', null, \'' . $e->getName() . '\'); })</script>'
			   . '</div>';			
	
		return $xhtml;
	}
	
}