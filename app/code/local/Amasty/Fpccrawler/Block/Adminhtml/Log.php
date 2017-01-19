<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Fpccrawler
 */
class Amasty_Fpccrawler_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_log';
        $this->_blockGroup = 'amfpccrawler';
        $this->_headerText = Mage::helper('amfpccrawler')->__('Log');
        $this->_removeButton('add');
        $this->_addButton('flush', array(
                'label'   => 'Flush Log',
                'onclick' => 'setLocation(\'' . Mage::helper("adminhtml")->getUrl("adminhtml/amfpccrawler_log/flush") . '\')',
            )
        );
    }
}