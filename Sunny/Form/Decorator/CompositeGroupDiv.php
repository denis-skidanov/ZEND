<?php

class Sunny_Form_Decorator_CompositeGroupDiv extends Zend_Form_Decorator_Abstract
{
	protected $_namespace = 'form-composite-group';
	
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
		$form = $this->getElement();
		
		if ((!$form instanceof Zend_Form) && (!$form instanceof Zend_Form_DisplayGroup)) {
			return $content;
		}
		
		$translator = $form->getTranslator();
		$view       = $form->getView();
		$items      = array();
		$separator  = $this->getSeparator();
		
		foreach ($form as $item) {
			$item->setView($view);
			$item->setTranslator($translator);
			
			$items[] = $item->render();
		}
		
		$eContent = implode(PHP_EOL, $items);
		$options = array('legend' => $form->getLegend());
		
		$name   = $form->getName();
		$class  = $this->_namespace;
		$class .= !empty($name) ? ' ' . $this->_namespace . '-' . $name : '';
				
		$xhtml = '<div class="' . $class . '" id="' . $this->_namespace . '-' . $name . '">'
		       . $view->fieldset($name, $eContent, array('legend' => $form->getLegend()))
		       . '</div>';
		
		switch ($this->getPlacement()) {
			case self::PREPEND:
				return $xhtml . $separator . $content;
			case self::APPEND:
			default:
				return $content . $separator . $xhtml;
		}
	}
}