<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to suporte.developer@buscape-inc.com so we can send you a copy immediately.
 *
 * @category   Buscape
 * @package    Navegg_Analytics
 * @copyright  Copyright (c) 2015 Navegg (http://www.navegg.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Navegg_Analytics_Model_Observer
{
    const BLOCK_PAGE_HEAD = 'Navegg_Analytics_Block_Page_Head';

    const BLOCK_PAGE_BODY = 'Navegg_Analytics_Block_Page_Body';

    private static $_config;

    private function _getConfig()
    {
        if(is_null(self::$_config)) {
            self::$_config = Mage::getModel('navegg/config');
        }
        return self::$_config;
    }

    public function setPageHeadNavegg(Varien_Event_Observer $observer)
    {
        $head = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('head');
        if ($head) {
            $block = Mage::app()->getFrontController()->getAction()->getLayout()->createBlock(
                self::BLOCK_PAGE_HEAD,
                'navegg_page_head',
                array(
                    'as' => 'navegg_page_head'
                )
            );
            $block->setNavegg($this->_getConfig()->getAccount());
            $head->insert($block);
        }
        $content = Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('content');
        if ($content && !is_null(Mage::registry('product'))) {
            $block = Mage::app()->getFrontController()->getAction()->getLayout()->createBlock(
                self::BLOCK_PAGE_BODY,
                'navegg_page_body',
                array(
                    'as' => 'navegg_page_body'
                )
            );
            $block->setProductName(Mage::registry('product')->getName());
            $content->insert($block);
        }
    }
}