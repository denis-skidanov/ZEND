<?php

class Sunny_Form_Decorator_CompositeElementDiv extends Zend_Form_Decorator_Abstract
{
	/**
	 * Css class prefix for decorator
	 * 
	 * @var string
	 */
	protected $_namespace = 'form-composite-element';
	
	/**
	 * Render composite element label tag
	 * 
	 * @return string XHTML
	 */
	public function buildLabel()
	{
		$e     = $this->getElement();
		$view  = $e->getView();
		$label = $e->getLabel();
		
		if (($translator = $e->getTranslator())) {
			$label = $translator->translate($label);
		}
		
		$text = '<span class="' . $this->_namespace . '-label-text">' . $label . '</span>';
		$class = $this->_namespace . '-label';
		
		$required = '';
		if ($e->isRequired()) {
			$required = '<span class="' . $this->_namespace . '-label-wildcard">*</span>';
			$class .= ' ' . $this->_namespace . '-label-required';
		}
		
		$attribs = array('escape' => false);		
		$xhtml = '<div class="' . $class . '">'
		       . $view->formLabel($e->getName(), $text . $required, $attribs)
		       . '</div>';
		
		return $xhtml;
	}
	
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
		if ('Zend_Form_Element_Hidden' == $type) {
			$xhtml = $view->$helper($e->getName(), $e->getValue(), $e->getAttribs(), $e->options);
		} else {
			$xhtml = '<div class="' . $this->_namespace . '-tag ' . $helper . '">'
			       . $view->$helper($e->getName(), $e->getValue(), $e->getAttribs(), $e->options)
			       . '</div>';			
		}
		
		return $xhtml;
	}
	
	/**
	 * Render error messages tag
	 * 
	 * @return string XHTML
	 */
	public function buildErrors()
	{
		$e        = $this->getElement();
		$view     = $e->getView();
		$messages = $e->getMessages();		
		$helper   = $view->getHelper('formErrors');
		
		$errors = '';
		if (!empty($messages)) {
			$helper->setElementStart('<div>');
			$helper->setElementSeparator('</div>' . PHP_EOL . '<div>');
			$helper->setElementEnd('</div>');
			$errors = $helper->formErrors($messages);
		}
		
		$xhtml = '<div class="' . $this->_namespace . '-errors">' . $errors . '</div>';
		return $xhtml;
	}
	
	/**
	 * Render description tag
	 * 
	 * @return string XHTML
	 */
	public function buildDescription()
	{
		$e    = $this->getElement();
		$desc = $e->getDescription();
		
		$xhtml = '<div class="' . $this->_namespace . '-description">' . $desc . '</div>';
		return $xhtml;
	}
	
	/**
	 * Render composite decorator
	 * 
	 * (non-PHPdoc)
	 * @see Zend_Form_Decorator_Abstract::render()
	 * 
	 * @return string XHTML
	 */
	public function render($content)
	{
		$element = $this->getElement();
		
		if (!$element instanceof Zend_Form_Element) {
			return $content;
		}
		
		if (null === $element->getView()) {
			return $content;
		}
	
		$separator = $this->getSeparator();
		$placement = $this->getPlacement();
		$type      = $element->getType();
		
		$label     = $this->buildLabel();
		$input     = $this->buildElement();
		$errors    = $this->buildErrors();
		$desc      = $this->buildDescription();
	
		$class  = $this->_namespace . ' ';
		$class .= $this->_namespace . '-' . $element->getName();
		
		$output = '<div class="' . $class . '">'
		        . $label
		        . $input
		        . $errors
		        . $desc
		        . '</div>';
		
		if ('Zend_Form_Element_Hidden' == $type) {
			$output = $input;
		}
	 
		switch ($placement) {
			case (self::PREPEND):
				return $output . $separator . $content;
			case (self::APPEND):
			default:
				return $content . $separator . $output;
		}
	}
}