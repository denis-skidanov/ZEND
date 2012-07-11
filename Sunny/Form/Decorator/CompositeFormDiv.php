<?php

class Sunny_Form_Decorator_CompositeFormDiv extends Zend_Form_Decorator_Abstract
{
	protected $_namespace = 'form-composite';
	
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
		
		$xhtml = '<div class="' . $this->_namespace . ($form->getName() ? ' ' . $form->getName() : '') . '">'
		       . $view->form($form->getName(), $form->getAttribs(), $eContent)
		       . '</div><div class="' . $this->_namespace . '-end"></div>';
		
		switch ($this->getPlacement()) {
			case self::PREPEND:
				return $xhtml . $separator . $content;
			case self::APPEND:
			default:
				return $content . $separator . $xhtml;
		}
	}
}