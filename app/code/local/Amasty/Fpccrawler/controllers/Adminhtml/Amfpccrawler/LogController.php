<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Fpccrawler
 */
class Amasty_Fpccrawler_Adminhtml_Amfpccrawler_LogController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('report/amfpccrawler');
        $this->_addContent($this->getLayout()->createBlock('amfpccrawler/adminhtml_log'));
        $this->renderLayout();
    }

    public function flushAction()
    {
        Mage::getResourceModel('amfpccrawler/log')->flushLog();
        $this->_redirect('*/*/index');

        return true;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            'report/amfpccrawler/amfpccrawler_log'
        );
    }

    protected function _setActiveMenu($menuPath)
    {
        $this->getLayout()->getBlock('menu')->setActive($menuPath);
        $this->_title($this->__('Reports'))->_title($this->__('FPC Crawler Log'));

        return $this;
    }

    protected function _title($text = NULL, $resetIfExists = true)
    {
        if (Mage::helper('ambase')->isVersionLessThan(1, 4)) {
            return $this;
        }

        return parent::_title($text, $resetIfExists);
    }
}
