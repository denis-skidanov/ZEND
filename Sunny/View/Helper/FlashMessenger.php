<?php

class Sunny_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract
{
    public function flashMessenger()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper("FlashMessenger");
    }
}
